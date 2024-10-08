<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Middlewares\AllowSameOriginIframeInEditor;
use BagistoPlus\Visual\Middlewares\InjectThemeEditorScript;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerConfig();
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/admin.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'visual');

        $this->bootMiddlewares();
        $this->bootViewEventListeners();
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/admin-menu.php', 'menu.admin');
        $this->mergeConfigFrom(__DIR__.'/../../config/viters.php', 'bagisto-vite.viters');
    }

    protected function bootMiddlewares()
    {
        /** @var \Illuminate\Foundation\Http\Kernel */
        $kernel = $this->app[Kernel::class];

        $kernel->prependMiddleware(AllowSameOriginIframeInEditor::class);
        $kernel->pushMiddleware(InjectThemeEditorScript::class);
    }

    protected function bootViewEventListeners()
    {
        Event::listen('bagisto.admin.layout.head.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('visual::admin.layouts.style');
        });
    }
}
