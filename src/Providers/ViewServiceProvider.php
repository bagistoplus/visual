<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Theme\Themes;
use BagistoPlus\Visual\View\JsonViewCompiler;
use BagistoPlus\Visual\View\VisualTagsCompiler;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerJsonViewCompiler();
        $this->registerEngineResolver();
    }

    public function boot()
    {
        $this->registerVisualTagsCompiler();
        $this->registerViewExtensions();
        $this->app->singleton('themes', fn () => new Themes);
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
}
