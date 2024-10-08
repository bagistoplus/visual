<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\Theme\Theme;

class ThemesController extends Controller
{
    public function index()
    {
        $themes = collect(config('themes.shop'))
            ->where('visual_theme', true)
            ->map(fn ($attrs) => Theme::make($attrs));

        return view('visual::admin.themes.index', [
            'themes' => $themes,
        ]);
    }
}
