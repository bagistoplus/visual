<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Blocks\BladeSection;
use BagistoPlus\Visual\Blocks\LivewireSection;
use BagistoPlus\Visual\Blocks\SimpleSection;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\Persistence\CreateTemplate;
use BagistoPlus\Visual\Persistence\PersistEditorUpdates;
use BagistoPlus\Visual\Persistence\PersistThemeSettings;
use BagistoPlus\Visual\Persistence\PublishTheme;
use BagistoPlus\Visual\Persistence\RenderPreview;
use BagistoPlus\Visual\Settings\Support\ImageTransformer;
use BagistoPlus\Visual\Settings\Support\VideoTransformer;
use BagistoPlus\Visual\Support\ChannelThemeResolver;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\ThemeEditor;
use BagistoPlus\Visual\ThemeSettingsLoader;
use BladeUI\Icons\Factory;
use BladeUI\Icons\IconsManifest;
use Craftile\Laravel\BlockSchemaRegistry;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
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
        protected PageRepository $pageRepository,
        protected PersistEditorUpdates $persistEditorUpdates,
        protected PersistThemeSettings $persistThemeSettings,
        protected PublishTheme $publishTheme,
        protected RenderPreview $renderPreview,
        protected ThemeSettingsLoader $themeSettingsLoader,
        protected TemplateDiscovery $templateDiscovery,
        protected CreateTemplate $createTemplate,
        protected ChannelThemeResolver $channelThemeResolver
    ) {}

    public function index(string $themeCode)
    {
        Craftile::registerDiscoveredSchemas();

        return view()->make('visual::admin.editor.index', [
            'config' => [
                'baseUrl' => parse_url(route('visual.admin.editor', ['theme' => $themeCode]), PHP_URL_PATH),
                'imagesBaseUrl' => Storage::disk(Visual::imagesDisk())->url(''),
                'videosBaseUrl' => Storage::disk(Visual::videosDisk())->url(''),
                'storefrontUrl' => url('/').'?'.http_build_query(['_designMode' => $themeCode]),
                'channels' => $this->getChannels(),
                'defaultChannel' => core()->getDefaultChannelCode(),
                'blockSchemas' => $this->loadBlocks(),
                'theme' => $this->loadTheme($themeCode),
                'templates' => $this->loadTemplates($themeCode),
                'routes' => [
                    'themesIndex' => route('visual.admin.themes.index'),
                    'persistUpdates' => route('visual.admin.editor.api.persist'),
                    'persistThemeSettings' => route('visual.admin.editor.api.persist_settings'),
                    'publishTheme' => route('visual.admin.editor.api.publish'),
                    'createTemplate' => route('visual.admin.editor.api.templates.create'),
                    'uploadImage' => route('visual.admin.editor.api.upload'),
                    'listImages' => route('visual.admin.editor.api.images'),
                    'uploadVideo' => route('visual.admin.editor.api.videos.upload'),
                    'listVideos' => route('visual.admin.editor.api.videos.index'),
                    'getCmsPages' => route('visual.admin.editor.api.cms_pages'),
                    'getIcons' => route('visual.admin.editor.api.icons'),
                ],
                'messages' => Lang::get('visual::theme-editor'),
                'editorLocale' => app()->getLocale(),
                'haveEdits' => $this->checkIfHaveEdits($themeCode),
            ],
        ]);
    }

    public function persistUpdates(Request $request)
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

        $result = $this->persistEditorUpdates->handle($validated);
        $loadedBlocks = $result['loadedBlocks'] ?? [];

        $allBlockIds = $this->buildRenderSet($validated['updates'], $loadedBlocks);

        $url = $request->input('template.url');

        return $this->renderPreview->execute($url, $allBlockIds);
    }

    public function persistThemeSettings(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getVisualThemes())],
            'channel' => ['required', 'string', Rule::in($this->getChannelCodes())],
            'locale' => ['required', 'string', Rule::in($this->getLocaleCodes($request->input('channel')))],
            'template' => ['required', 'array'],
            'template.url' => ['required', 'string'],
            'updates' => ['required', 'array'],
        ]);

        $this->persistThemeSettings->handle($validated);

        $url = $request->input('template.url');

        return $this->renderPreview->execute($url);
    }

    public function publishTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string'],
            'channel' => ['nullable', 'string', Rule::in($this->getChannelCodes())],
            'locale' => ['nullable', 'string'],
            'template' => ['nullable', 'string'],
            'page' => ['nullable', 'array'],
        ]);

        // If page data is provided, persist it first before publishing
        if (isset($validated['page'])) {
            $this->persistEditorUpdates->handleFullPage($validated);
        }

        $this->publishTheme->handle($validated['theme']);

        return response()->json([
            'success' => true,
            'message' => 'Theme published successfully',
        ]);
    }

    public function createTemplate(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getVisualThemes())],
            'channel' => ['required', 'string', Rule::in($this->getChannelCodes())],
            'locale' => ['required', 'string', Rule::in($this->getLocaleCodes($request->input('channel')))],
            'type' => ['required', 'string', Rule::in(TemplateDiscovery::ASSIGNABLE_TYPES)],
            'name' => ['required', 'string', 'max:25'],
            'basedOn' => ['nullable', 'string'],
        ]);

        $theme = Theme::make(config("themes.shop.{$validated['theme']}"));

        try {
            $template = ($this->createTemplate)(
                theme: $theme,
                channel: $validated['channel'],
                locale: $validated['locale'],
                type: $validated['type'],
                name: $validated['name'],
                basedOn: $validated['basedOn'] ?? null,
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $templates = collect($this->loadTemplates($validated['theme']));
        $baseTemplate = $templates->firstWhere('template', $validated['type']);
        $createdTemplate = [
            'template' => $template['key'],
            'label' => $template['label'],
            'icon' => $baseTemplate['icon'] ?? '',
            'previewUrl' => $baseTemplate['previewUrl'] ?? '',
            'type' => $validated['type'],
            'supportsVariants' => false,
            'isJsonTemplate' => true,
        ];

        return response()->json([
            'template' => $template,
            'createdTemplate' => $createdTemplate,
            'templates' => $templates
                ->reject(fn ($existing) => $existing['template'] === $createdTemplate['template'])
                ->push($createdTemplate)
                ->values()
                ->all(),
        ]);
    }

    public function uploadImages(Request $request)
    {
        /** @var Collection<int, UploadedFile> $images */
        $images = collect($request->file('image'));

        return $images->map(function ($image) {
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->guessExtension();
            $storedName = bin2hex($originalName).'_'.uniqid().'.'.$extension;

            $path = $image->storeAs(
                Visual::imagesDirectory(),
                $storedName,
                Visual::imagesDisk(),
            );

            return [
                'path' => $path,
                'name' => $originalName,
                'url' => Storage::disk(Visual::imagesDisk())->url($path),
            ];
        });
    }

    public function listImages()
    {
        $diskName = Visual::imagesDisk();
        $files = Storage::disk($diskName)
            ->files(Visual::imagesDirectory());

        return collect($files)->map(function ($file) {
            $image = (new ImageTransformer)($file);

            return [
                'name' => $image->name,
                'path' => $image->path,
                'url' => $image->url,
            ];
        });
    }

    public function uploadVideos(Request $request)
    {
        $request->validate([
            'video' => ['required', 'array'],
            'video.*' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/x-m4v,video/webm,video/ogg',
                'max:'.Visual::videosMaxUploadSize(),
            ],
        ]);

        /** @var Collection<int, UploadedFile> $videos */
        $videos = collect($request->file('video'));

        return $videos->map(function ($video) {
            $originalName = pathinfo($video->getClientOriginalName(), PATHINFO_FILENAME);
            $mimeType = $video->getMimeType();
            $extension = match ($mimeType) {
                'video/mp4', 'video/x-m4v' => 'mp4',
                'video/webm' => 'webm',
                'video/ogg' => 'ogg',
                default => $video->guessExtension(),
            };
            $storedName = bin2hex($originalName).'_'.uniqid().'.'.$extension;

            $path = $video->storeAs(
                Visual::videosDirectory(),
                $storedName,
                Visual::videosDisk(),
            );

            return [
                'path' => $path,
                'name' => $originalName,
                'url' => Storage::disk(Visual::videosDisk())->url($path),
                'mime_type' => $mimeType,
            ];
        });
    }

    public function listVideos()
    {
        $diskName = Visual::videosDisk();
        $files = Storage::disk($diskName)
            ->files(Visual::videosDirectory());

        return collect($files)->map(function ($file) {
            $video = (new VideoTransformer)->transform($file);

            return [
                'name' => $video->name,
                'path' => $video->path,
                'url' => $video->url,
                'mime_type' => $video->sources[0]['mime_type'] ?? null,
            ];
        });
    }

    public function cmsPages(Request $request)
    {
        $currentLocale = core()->getRequestedLocaleCode();

        return Page::query()
            ->select('cms_pages.id')
            ->join('cms_page_translations', function ($join) use ($currentLocale) {
                $join->on('cms_pages.id', '=', 'cms_page_translations.cms_page_id')
                    ->where('cms_page_translations.locale', '=', $currentLocale);
            })
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

    protected function loadBlocks()
    {
        /** @var Collection<string, BlockSchema> $schemas */
        $schemas = collect(app(BlockSchemaRegistry::class)->all());

        return $schemas->map(function (BlockSchema $blockSchema) {
            $currentGroup = null;
            $properties = collect($blockSchema->properties)
                ->map(function ($prop) use (&$currentGroup) {
                    $propArray = $prop->toArray();

                    if ($propArray['type'] === 'header') {
                        $currentGroup = $propArray['label'];

                        return null;
                    }

                    // Filter out typography_presets as it's theme settings only
                    if ($propArray['type'] === 'typography_presets') {
                        return null;
                    }

                    if ($currentGroup !== null) {
                        $propArray['group'] = $currentGroup;
                    }

                    return $propArray;
                })
                ->filter()
                ->values();

            $meta = array_merge($blockSchema->meta, [
                'name' => $blockSchema->name,
                'icon' => $blockSchema->icon,
                'category' => $blockSchema->category,
                'description' => $blockSchema->description,
                'previewImageUrl' => $blockSchema->previewImageUrl,
                'isSection' => collect([SimpleSection::class, BladeSection::class, LivewireSection::class])->some(fn ($class) => is_subclass_of($blockSchema->class, $class)),
                'enabledOn' => $blockSchema->enabledOn,
                'disabledOn' => $blockSchema->disabledOn,
            ]);

            return [
                'type' => $blockSchema->type,
                'properties' => $properties,
                'accepts' => $blockSchema->accepts,
                'presets' => $blockSchema->presets,
                'private' => $blockSchema->private,
                'meta' => $meta,
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

    protected function loadTemplates(?string $themeCode = null)
    {
        $templates = collect(app(ThemeEditor::class)->getTemplates())
            ->map(fn ($template) => [
                'template' => $template->template,
                'label' => $template->label,
                'icon' => $template->icon,
                'previewUrl' => $template->previewUrl,
                'type' => $template->type,
                'supportsVariants' => $template->supportsVariants,
                'isJsonTemplate' => false,
            ])
            ->values();

        $themeCode ??= request('theme');
        $theme = $themeCode ? Theme::make(array_merge(['code' => $themeCode], config("themes.shop.{$themeCode}", []))) : null;

        if ($theme?->isVisualTheme()) {
            foreach (TemplateDiscovery::ASSIGNABLE_TYPES as $type) {
                $typeTemplates = $this->templateDiscovery
                    ->forType($theme, $type, core()->getDefaultChannelCode(), app()->getLocale(), true);

                $defaultTemplate = $typeTemplates->firstWhere('key', $type);
                $baseTemplate = $templates->firstWhere('template', $type);

                if ($defaultTemplate?->isJsonTemplate) {
                    $templates = $templates->map(
                        fn ($template) => $template['template'] === $type
                            ? array_merge($template, ['isJsonTemplate' => true])
                            : $template
                    );
                }

                $customTemplates = $typeTemplates
                    ->reject(fn ($template) => $template->key === $type)
                    ->map(fn ($template) => [
                        'template' => $template->key,
                        'label' => $template->label,
                        'icon' => $baseTemplate['icon'] ?? '',
                        'previewUrl' => $baseTemplate['previewUrl'] ?? '',
                        'type' => $type,
                        'supportsVariants' => false,
                        'isJsonTemplate' => $template->isJsonTemplate,
                    ]);

                $templates = $templates->merge($customTemplates);
            }
        }

        return $templates->values()->toArray();
    }

    protected function checkIfHaveEdits(string $themeCode): bool
    {
        $lastEditFile = ThemePathsResolver::getThemeBaseDataPath($themeCode, 'editor/.last-edit');

        return file_exists($lastEditFile);
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

    /**
     * Build complete render set: changed blocks + all their parents + all their children.
     */
    protected function buildRenderSet(array $updates, array $loadedBlocks): array
    {
        $changes = $updates['changes'] ?? [];
        $addedIds = $changes['added'] ?? [];
        $changedIds = array_keys($updates['blocks']);
        // dd($changedIds, $loadedBlocks);
        $renderSet = [];

        foreach ($changedIds as $id) {
            $renderSet[] = $id;

            // If block is in added and has a parent, include parent's children
            if (in_array($id, $addedIds) && isset($loadedBlocks[$id]['parentId']) && $loadedBlocks[$id]['parentId']) {
                $parentId = $loadedBlocks[$id]['parentId'];
                $this->addChildren($parentId, $loadedBlocks, $renderSet);
            }

            $currentId = $id;
            while (isset($loadedBlocks[$currentId]['parentId']) && $loadedBlocks[$currentId]['parentId']) {
                $parentId = $loadedBlocks[$currentId]['parentId'];
                $renderSet[] = $parentId;

                // If parent is a repeated block, include children of the repeated block's parent
                if (isset($loadedBlocks[$parentId]['repeated']) && $loadedBlocks[$parentId]['repeated']) {
                    if (isset($loadedBlocks[$parentId]['parentId']) && $loadedBlocks[$parentId]['parentId']) {
                        $this->addChildren($loadedBlocks[$parentId]['parentId'], $loadedBlocks, $renderSet);
                    }
                }

                $currentId = $parentId;
            }

            $this->addChildren($id, $loadedBlocks, $renderSet);
        }

        return array_unique($renderSet);
    }

    /**
     * Recursively add all children to the render set.
     */
    protected function addChildren(string $blockId, array $loadedBlocks, array &$renderSet): void
    {
        if (! isset($loadedBlocks[$blockId]['children'])) {
            return;
        }

        foreach ($loadedBlocks[$blockId]['children'] as $childId) {
            $renderSet[] = $childId;
            $this->addChildren($childId, $loadedBlocks, $renderSet);
        }
    }
}
