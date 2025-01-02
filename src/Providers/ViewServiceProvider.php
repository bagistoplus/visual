<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\LivewireFeatures\SupportSectionData;
use BagistoPlus\Visual\Theme\Themes;
use BagistoPlus\Visual\ThemePathsResolver;
use BagistoPlus\Visual\View\BladeDirectives;
use BagistoPlus\Visual\View\JsonViewCompiler;
use BagistoPlus\Visual\View\VisualTagsCompiler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine;
use Webkul\Core\Repositories\ChannelRepository;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerJsonViewCompiler();
        $this->registerEngineResolver();

        $this->app->singleton('tets', function (Application $app) {
            return $app->make(ChannelRepository::class);
        });
    }

    public function boot()
    {
        $this->registerVisualTagsCompiler();
        $this->registerBladeDirectives();
        $this->registerViewExtensions();

        $this->bootLivewireFeatures();
        $this->bootViewComposers();

        $this->app->singleton('themes', fn() => new Themes);
        $this->app->singleton(ThemePathsResolver::class, function (Application $app) {
            return new ThemePathsResolver;
        });
    }

    protected function registerVisualTagsCompiler()
    {
        $this->app['blade.compiler']->precompiler(app(VisualTagsCompiler::class));
    }

    /**
     * Register the json template compiler.
     *
     * @return void
     */
    public function registerJsonViewCompiler()
    {
        $this->app->singleton('jsonview.compiler', function ($app) {
            return new JsonViewCompiler(
                $app['files'],
                $app['config']['view.compiled'],
                $app['blade.compiler']
            );
        });
    }

    protected function registerEngineResolver()
    {
        $this->app->extend('view.engine.resolver', function ($resolver) {
            $resolver->register('jsonview', function () {
                return new CompilerEngine($this->app['jsonview.compiler'], $this->app['files']);
            });

            return $resolver;
        });
    }

    protected function registerViewExtensions()
    {
        foreach (JsonViewCompiler::EXTENSIONS as $extension) {
            $this->app['view']->addExtension($extension, 'jsonview');
        }
    }

    protected function registerBladeDirectives()
    {
        Blade::directive('visual_layout_content', [BladeDirectives::class, 'visualLayoutContent']);
        Blade::directive('visual_content', [BladeDirectives::class, 'visualContent']);
        Blade::directive('end_visual_content', [BladeDirectives::class, 'endVisualContent']);
    }

    protected function bootLivewireFeatures()
    {
        app('livewire')->componentHook(SupportSectionData::class);
    }

    protected function bootViewComposers()
    {
        view()->composer('shop::*', function ($view) {
            $theme = app('themes')->current();

            if ($theme->isVisualTheme) {
                $theme->settings = Visual::themeDataCollector()->getThemeSettings();
                $view->with('theme', $theme);
            }
        });
    }
}
