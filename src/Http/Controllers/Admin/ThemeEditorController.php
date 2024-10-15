<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\Sections\Concerns\ImageTransformer;
use BagistoPlus\Visual\ThemePersister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ThemeEditorController extends Controller
{
    public function __construct(protected ThemePersister $themePersister) {}

    public function index($themeCode)
    {
        return view('visual::admin.editor.index', [
            'baseUrl' => route('visual.admin.editor', ['theme' => $themeCode], false),
            'imagesBaseUrl' => Storage::disk(config('bagisto_visual.images_storage'))->url(''),
            'storefrontUrl' => url('/').'?'.http_build_query(['_designMode' => $themeCode]),
            'channels' => $this->getChannels(),
            'defaultChannel' => app('core')->getDefaultChannelCode(),
            'routes' => [
                'themesIndex' => route('visual.admin.themes.index'),
                'persistTheme' => route('visual.admin.editor.api.persist'),
                'uploadImage' => route('visual.admin.editor.api.upload'),
                'listImages' => route('visual.admin.editor.api.images'),
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
            $storedName = bin2hex($originalName).'_'.uniqid().'.'.$extension;

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

    protected function getChannels()
    {
        return app('core')->getAllChannels()->map(fn ($channel) => [
            'code' => $channel->code,
            'name' => $channel->name,
            'locales' => $channel->locales,
            'default_locale' => $channel->default_locale->code,
        ]);
    }
}
