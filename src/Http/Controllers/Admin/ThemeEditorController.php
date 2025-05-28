<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\Settings\Support\ImageTransformer;
use BagistoPlus\Visual\ThemePersister;
use BladeUI\Icons\Factory;
use BladeUI\Icons\IconsManifest;
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
    public function __construct(protected ThemePersister $themePersister, protected PageRepository $pageRepository) {}

    public function index($themeCode)
    {
        return view('visual::admin.editor.index', [
            'config' => [
                'baseUrl' => route('visual.admin.editor', ['theme' => $themeCode], false),
                'imagesBaseUrl' => Storage::disk(config('bagisto_visual.images_storage'))->url(''),
                'storefrontUrl' => url('/').'?'.http_build_query(['_designMode' => $themeCode]),
                'channels' => $this->getChannels(),
                'defaultChannel' => core()->getDefaultChannelCode(),
                'sections' => Sections::all(),
                'routes' => [
                    'themesIndex' => route('visual.admin.themes.index'),
                    'persistTheme' => route('visual.admin.editor.api.persist'),
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

    public function persistTheme(Request $request)
    {
        $request->validate([
            'theme' => ['required', 'string', Rule::in($this->getVisualThemes())],
            'channel' => ['required', 'string', Rule::in($this->getChannelCodes())],
            'locale' => ['required', 'string', Rule::in($this->getLocaleCodes($request->input('channel')))],
        ]);

        $this->themePersister->persist($request->all());

        $request = Request::create($request->input('url'), 'GET');
        $response = app()->handle($request);

        return $response->getContent();
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
}
