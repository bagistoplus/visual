<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\Theme\Theme;

class ThemesController extends Controller
{
    public function index()
    {
        $themes = collect(config('themes.shop', []))
            ->map(fn (array $attributes, $code) => Theme::make(['code' => $code] + $attributes))
            ->filter(fn (Theme $theme) => $theme->editorMode()->isEditable())
            ->values();

        return view()->make('visual::admin.themes.index', [
            'themes' => $themes,
        ]);
    }
}
