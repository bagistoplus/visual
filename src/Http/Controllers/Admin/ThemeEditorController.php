<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Blocks\BladeSection;
use BagistoPlus\Visual\Blocks\LivewireSection;
use BagistoPlus\Visual\Blocks\SimpleSection;
use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\Persistence\PersistEditorUpdates;
use BagistoPlus\Visual\Settings\Support\ImageTransformer;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\ThemePersister;
use BagistoPlus\Visual\ThemeSettingsLoader;
use BladeUI\Icons\Factory;
use BladeUI\Icons\IconsManifest;
use Craftile\Core\Data\BlockSchema;
use Craftile\Laravel\BlockSchemaRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Webkul\CMS\Models\Page;
use Webkul\CMS\Repositories\PageRepository;

class ThemeEditorController extends Controller
{
    public function __construct(
        protected ThemePersister $themePersister,
        protected PageRepository $pageRepository,
        protected PersistEditorUpdates $persistEditorUpdates,
        protected ThemeSettingsLoader $themeSettingsLoader
    ) {}

    public function index($themeCode)
    {
        return view('visual::admin.editor.index', [
            'config' => [
                'baseUrl' => parse_url(route('visual.admin.editor', ['theme' => $themeCode]), PHP_URL_PATH),
                'imagesBaseUrl' => Storage::disk(config('bagisto_visual.images_storage'))->url(''),
                'storefrontUrl' => url('/').'?'.http_build_query(['_designMode' => $themeCode]),
                'channels' => $this->getChannels(),
                'defaultChannel' => core()->getDefaultChannelCode(),
                'blockSchemas' => $this->loadBlocks(),
                'theme' => $this->loadTheme($themeCode),
                'routes' => [
                    'themesIndex' => route('visual.admin.themes.index'),
                    'persistUpdates' => route('visual.admin.editor.api.persist'),
                    'persistThemeSettings' => route('visual.admin.editor.api.persist_settings'),
                    'publishTheme' => route('visual.admin.editor.api.publish'),
                    'uploadImage' => route('visual.admin.editor.api.upload'),
                    'listImages' => route('visual.admin.editor.api.images'),
                    'getCmsPages' => route('visual.admin.editor.api.cms_pages'),
                    'getIcons' => route('visual.admin.editor.api.icons'),
                ],
                'messages' => Lang::get('visual::theme-editor'),
                'editorLocale' => app()->getLocale(),
            ],
        ]);
    }

    protected function loadBlocks()
    {
        return collect(app(BlockSchemaRegistry::class)->all())
            ->map(function (BlockSchema $blockSchema) {
                $currentGroup = null;
                $properties = collect($blockSchema->properties)
                    ->map(function ($prop) use (&$currentGroup) {
                        $propArray = $prop->toArray();

                        if ($propArray['type'] === 'header') {
                            $currentGroup = $propArray['label'];

                            return null;
                        }

                        if ($currentGroup !== null) {
                            $propArray['group'] = $currentGroup;
                        }

                        return $propArray;
                    })
                    ->filter() // Remove null values (headers)
                    ->values();

                return [
                    'type' => $blockSchema->type,
                    'properties' => $properties,
                    'accepts' => $blockSchema->accepts,
                    'presets' => $blockSchema->presets,
                    'meta' => [
                        'name' => $blockSchema->name,
                        'icon' => $blockSchema->icon,
                        'category' => $blockSchema->category,
                        'description' => $blockSchema->description,
                        'previewImageUrl' => asset($blockSchema->previewImageUrl),
                        'is_section' => collect([SimpleSection::class, BladeSection::class, LivewireSection::class])->some(fn ($class) => is_subclass_of($blockSchema->class, $class)),
                    ],
                ];
            })
            ->values();
    }

    protected function loadTheme($themeCode)
    {
        $themeConfig = config("themes.shop.{$themeCode}");

        if (! $themeConfig) {
            abort(404, "Theme {$themeCode} not found");
        }

        $theme = Theme::make($themeConfig);

        $settingsBag = $this->themeSettingsLoader->loadThemeSettings($theme);

        return [
            'name' => $theme->name,
            'code' => $theme->code,
            'version' => $theme->version ?? '1.0.0',
            'settings' => $settingsBag->toArray(),
            'settingsSchema' => $this->translateSettingsSchema($theme->settingsSchema),
        ];
    }

    public function persistTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getVisualThemes())],
            'channel' => ['required', 'string', Rule::in($this->getChannelCodes())],
            'locale' => ['required', 'string', Rule::in($this->getLocaleCodes($request->input('channel')))],
            'template' => ['required', 'array'],
            'template.url' => ['required', 'string'],
            'template.name' => ['required', 'string'],
            'template.sources' => ['required', 'string'],
            'updates' => ['required', 'array'],
            'updates.changes' => ['required', 'array'],
            'updates.changes.added' => ['present', 'array'],
            'updates.changes.updated' => ['present', 'array'],
            'updates.changes.removed' => ['present', 'array'],
            'updates.changes.moved' => ['nullable', 'array'],
            'updates.blocks' => ['present', 'array'],
            'updates.regions' => ['present', 'array'],
        ]);

        $this->persistEditorUpdates->handle($validated);

        $effects = $this->computeEffects($validated, $request);

        return response()->json([
            'success' => true,
            'message' => 'Updates persisted successfully',
            'effects' => $effects,
        ]);
    }

    protected function computeEffects(array $data, Request $request): array
    {
        $effects = [
            'html' => [],
            'css' => [],
            'js' => [],
        ];

        $baseUrl = rtrim(config('app.url'));
        $url = $request->input('template.url');

        // if (! empty($sections = $result['updatedSections'] ?? [])) {
        //     $url .= '&' . http_build_query(['_sections' => implode(',', $sections)]);
        // }

        if (parse_url($baseUrl, PHP_URL_PATH) !== null) {
            // TODO: handle subdirectory installs
            $response = redirect($url);

            return [];
        }

        $request = Request::create($url, 'GET');
        $response = app()->handle($request);
        $html = $response->getContent();

        $blocks = array_unique(array_merge($data['updates']['changes']['added'], $data['updates']['changes']['updated']));

        foreach ($blocks as $blockId) {
            $effects['html'][$blockId] = $this->extractBlockHtml($html, $blockId);
        }

        return $effects;
    }

    protected function extractBlockHtml(string $html, string $blockId): ?string
    {
        // Look for the block element with data-block attribute
        $pattern = '/<([^>]+)data-block="'.preg_quote($blockId, '/').'"([^>]*)>/';

        if (! preg_match($pattern, $html, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $startPos = $matches[0][1];
        $openingTag = $matches[0][0];

        // Extract tag name to find closing tag
        if (! preg_match('/<(\w+)/', $openingTag, $tagMatches)) {
            return null;
        }

        $tagName = $tagMatches[1];

        // Handle self-closing tags
        if (str_ends_with(trim($openingTag), '/>')) {
            return $openingTag;
        }

        // Find matching closing tag by counting nested tags
        $searchPos = $startPos + strlen($openingTag);
        $depth = 1;
        $currentPos = $searchPos;

        while ($depth > 0 && $currentPos < strlen($html)) {
            $nextOpen = strpos($html, "<{$tagName}", $currentPos);
            $nextClose = strpos($html, "</{$tagName}>", $currentPos);

            if ($nextClose === false) {
                break; // No closing tag found
            }

            if ($nextOpen !== false && $nextOpen < $nextClose) {
                // Found nested opening tag
                $depth++;
                $currentPos = $nextOpen + strlen("<{$tagName}");
            } else {
                // Found closing tag
                $depth--;
                if ($depth === 0) {
                    // This is our matching closing tag
                    $endPos = $nextClose + strlen("</{$tagName}>");

                    return substr($html, $startPos, $endPos - $startPos);
                }
                $currentPos = $nextClose + strlen("</{$tagName}>");
            }
        }

        return null; // Could not find complete block HTML
    }

    public function persistThemeSettings(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getVisualThemes())],
            'channel' => ['required', 'string', Rule::in($this->getChannelCodes())],
            'locale' => ['required', 'string', Rule::in($this->getLocaleCodes($request->input('channel')))],
            'themeSettings' => ['required', 'array'],
        ]);

        // TODO: Persist theme settings to file
        // For now, just reload the page

        return response()->json([
            'success' => true,
            'message' => 'Theme settings updated successfully',
            'effects' => null, // This will trigger a full page reload
        ]);
    }

    public function publishTheme(Request $request)
    {
        $this->themePersister->publish($request->input('theme'));

        return 'Done';
    }

    public function uploadImages(Request $request)
    {
        /** @var \Illuminate\Support\Collection<int, \Illuminate\Http\UploadedFile> $images */
        $images = collect($request->file('image'));

        return $images->map(function ($image) {
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->guessExtension();
            $storedName = bin2hex($originalName).'_'.uniqid().'.'.$extension;

            $path = $image->storeAs(
                config('bagisto_visual.images_directory'),
                $storedName,
                config('bagisto_visual.images_storage'),
            );

            return [
                'path' => $path,
                'name' => $originalName,
                'url' => Storage::disk(config('bagisto_visual.images_storage'))->url($path),
            ];
        });
    }

    public function listImages()
    {
        $diskName = config('bagisto_visual.images_storage');
        $files = Storage::disk($diskName)
            ->files(config('bagisto_visual.images_directory'));

        return collect($files)->map(function ($file) {
            $image = (new ImageTransformer)($file);

            return [
                'name' => $image->name,
                'path' => $image->path,
                'url' => $image->url,
            ];
        });
    }

    public function cmsPages(Request $request)
    {
        $currentLocale = core()->getRequestedLocaleCode();

        return Page::query()
            ->select('cms_pages.id')
            // ->addSelect(DB::raw('GROUP_CONCAT(DISTINCT code) as channel'))
            ->join('cms_page_translations', function ($join) use ($currentLocale) {
                $join->on('cms_pages.id', '=', 'cms_page_translations.cms_page_id')
                    ->where('cms_page_translations.locale', '=', $currentLocale);
            })
            // ->leftJoin('cms_page_channels', 'cms_pages.id', '=', 'cms_page_channels.cms_page_id')
            // ->leftJoin('channels', 'cms_page_channels.channel_id', '=', 'channels.id')
            // ->groupBy('cms_pages.id', 'cms_page_translations.locale')
            ->when($request->has('query'), function ($query) use ($request) {
                $query->where('cms_page_translations.page_title', 'LIKE', "%{$request->query('query')}%");
            })
            ->get();
    }

    public function icons(Request $request, Factory $factory, IconsManifest $iconsManifest)
    {
        $sets = $factory->all();
        $selectedSet = $request->input('set', 'lucide');

        if (isset($sets['default'])) {
            unset($sets['default']);
        }

        $set = $sets[$selectedSet];
        $icons = collect();

        foreach ($set['paths'] as $path) {
            if (! File::isDirectory(($path))) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if ($file->getExtension() !== 'svg') {
                    continue;
                }

                $name = $file->getFilenameWithoutExtension();

                $icons->push([
                    'name' => $name,
                    'id' => $set['prefix'].'-'.$name,
                    'svg' => File::get($file->getRealPath()),
                ]);
            }
        }

        return [
            'currentSet' => $selectedSet,
            'sets' => collect($sets)->map(fn ($set, $key) => ['id' => $key, 'prefix' => $set['prefix'], 'name' => Str::headline($key)])->values(),
            'icons' => $icons->values(),
        ];
    }

    protected function getChannels()
    {
        return core()->getAllChannels()->map(fn ($channel) => [
            'code' => $channel->code,
            'name' => $channel->name,
            'locales' => $channel->locales,
            'default_locale' => $channel->default_locale->code,
        ]);
    }

    protected function getChannelCodes(): array
    {
        return $this->getChannels()
            ->map(fn ($channel) => $channel['code'])
            ->toArray();
    }

    protected function getLocaleCodes(string $channel): array
    {
        $channel = $this->getChannels()->firstWhere('code', $channel);

        if (! $channel) {
            return [];
        }

        return $channel['locales']->map(fn ($locale) => $locale['code'])->toArray();
    }

    protected function getVisualThemes(): array
    {
        return collect(config('themes.shop', []))
            ->filter(fn ($config) => $config['visual_theme'] ?? false)
            ->map(fn ($config) => $config['code'])
            ->toArray();
    }

    protected function translateSettingsSchema(array $settingsSchema): array
    {
        return collect($settingsSchema)->map(function ($group) {
            $group['name'] = trans($group['name']);

            $group['settings'] = collect($group['settings'])->map(function ($setting) {
                $setting['label'] = trans($setting['label']);
                if (isset($setting['info'])) {
                    $setting['info'] = trans($setting['info']);
                }

                return $setting;
            })->all();

            return $group;
        })->all();
    }
}
