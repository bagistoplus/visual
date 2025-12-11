<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\Theme\Themes;
use BagistoPlus\Visual\ThemePathsResolver;
use BagistoPlus\Visual\View\BladeDirectives;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerBladeDirectives();
        $this->bootViewComposers();

        $this->app->bind(\Webkul\Theme\Themes::class, Themes::class);
        $this->app->singleton('themes', fn () => new Themes);

        $this->app->singleton(ThemePathsResolver::class, function () {
            return new ThemePathsResolver;
        });
    }

    protected function registerBladeDirectives()
    {
        Blade::directive('style', [BladeDirectives::class, 'style']);
        Blade::directive('endstyle', [BladeDirectives::class, 'endStyle']);

        Blade::directive('visual_design_mode', [BladeDirectives::class, 'visualDesignMode']);
        Blade::directive('end_visual_design_mode', [BladeDirectives::class, 'endVisualDesignMode']);

        Blade::if('visualdesignmode', function () {
            return ThemeEditor::inDesignMode();
        });

        Blade::directive('visual_color_vars', [BladeDirectives::class, 'visualColorVars']);
    }

    protected function bootViewComposers()
    {
        view()->composer('shop::*', function ($view) {
            $theme = themes()->current();

            if ($theme instanceof Theme && $theme->isVisualTheme) {
                $theme->settings = Visual::themeSettingsLoader()->loadThemeSettings($theme);
                $view->with('theme', $theme);
            }
        });

        view()->composer('shop::layouts.account', function ($view) {
            if (auth('customer')->check()) {
                $view->with('customer', auth('customer')->user());
            }
        });
    }
}
