<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\Sections\Concerns\ImageTransformer;
use BagistoPlus\Visual\ThemePersister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\CMS\Models\Page;
use Webkul\CMS\Repositories\PageRepository;

class ThemeEditorController extends Controller
{
    public function __construct(protected ThemePersister $themePersister, protected PageRepository $pageRepository) {}

    public function index($themeCode)
    {
        return view('visual::admin.editor.index', [
            'baseUrl' => route('visual.admin.editor', ['theme' => $themeCode], false),
            'imagesBaseUrl' => Storage::disk(config('bagisto_visual.images_storage'))->url(''),
            'storefrontUrl' => url('/') . '?' . http_build_query(['_designMode' => $themeCode]),
            'channels' => $this->getChannels(),
            'defaultChannel' => app('core')->getDefaultChannelCode(),
            'sections' => Sections::all(),
            'routes' => [
                'themesIndex' => route('visual.admin.themes.index'),
                'persistTheme' => route('visual.admin.editor.api.persist'),
                'uploadImage' => route('visual.admin.editor.api.upload'),
                'listImages' => route('visual.admin.editor.api.images'),
                'getCmsPages' => route('visual.admin.editor.api.cms_pages'),
            ],
        ]);
    }

    public function persistTheme(Request $request)
    {
        return $this->themePersister->persist($request->all());
    }

    public function uploadImages(Request $request)
    {
        return collect($request->file('image'))->map(function ($file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->guessExtension();
            $storedName = bin2hex($originalName) . '_' . uniqid() . '.' . $extension;

            $path = $file->storeAs(
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
        $currentLocale = app('core')->getRequestedLocaleCode();

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

    protected function getChannels()
    {
        return app('core')->getAllChannels()->map(fn($channel) => [
            'code' => $channel->code,
            'name' => $channel->name,
            'locales' => $channel->locales,
            'default_locale' => $channel->default_locale->code,
        ]);
    }
}
