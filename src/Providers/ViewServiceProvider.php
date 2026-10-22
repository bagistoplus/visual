<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\BagistoThemeColors;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\Theme\Themes;
use BagistoPlus\Visual\ThemePathsResolver;
use BagistoPlus\Visual\ThemeSettingsLoader;
use BagistoPlus\Visual\View\BladeDirectives;
use BagistoPlus\Visual\View\Composers\DraftedSectionsComposer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(\Webkul\Theme\Themes::class, Themes::class);

        $this->app->singleton(ThemePathsResolver::class, function () {
            return new ThemePathsResolver;
        });
    }

    public function boot()
    {
        $this->registerBladeDirectives();
        $this->bootViewComposers();
        $this->bootThemeColors();
    }

    /**
     * Put the merchant's colours over the ones a theme Bagisto draws the
     * sections of was compiled with.
     *
     * A visual theme writes its own settings into its own markup and needs
     * none of this. The stylesheet is injected on the storefront as well as in
     * the preview, since the editor would otherwise be showing a colour the
     * shop never gets.
     */
    protected function bootThemeColors()
    {
        Event::listen('bagisto.shop.layout.head.after', function ($viewRenderEventManager) {
            $theme = themes()->current();

            if (! $theme instanceof Theme || ! $theme->editorMode()->isBagisto()) {
                return;
            }

            $css = app(BagistoThemeColors::class)->css(
                $theme->customize,
                app(ThemeSettingsLoader::class)->loadThemeSettings($theme)->toArray(),
            );

            if ($css === '') {
                return;
            }

            $viewRenderEventManager->addTemplate('visual::bagisto.theme-colors', [
                'visualThemeColors' => $css,
            ]);
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

        Blade::directive('fpcRegion', [BladeDirectives::class, 'fpcRegion']);
        Blade::directive('endFpcRegion', [BladeDirectives::class, 'endFpcRegion']);

        Blade::directive('fpcRegionOnce', [BladeDirectives::class, 'fpcRegionOnce']);
        Blade::directive('endFpcRegionOnce', [BladeDirectives::class, 'endFpcRegionOnce']);

        Blade::directive('visual_color_vars', [BladeDirectives::class, 'visualColorVars']);
    }

    protected function bootViewComposers()
    {
        view()->composer('shop::layouts.account', function ($view) {
            if (auth('customer')->check()) {
                $view->with('customer', auth('customer')->user());
            }
        });

        view()->composer('shop::home.index', DraftedSectionsComposer::class.'@compose');
    }
}
