<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\ThemePersister;
use Illuminate\Http\Request;

class ThemeEditorController extends Controller
{
    public function __construct(protected ThemePersister $themePersister) {}

    public function index($themeCode)
    {
        return view('visual::admin.editor.index', [
            'baseUrl' => route('visual.admin.editor', ['theme' => $themeCode], false),
            'storefrontUrl' => url('/').'?'.http_build_query(['_designMode' => $themeCode]),
            'channels' => $this->getChannels(),
            'defaultChannel' => app('core')->getDefaultChannelCode(),
            'routes' => [
                'persistTheme' => route('visual.admin.editor.api.persist'),
            ],
        ]);
    }

    public function persistTheme(Request $request)
    {
        return $this->themePersister->persist($request->all());
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
