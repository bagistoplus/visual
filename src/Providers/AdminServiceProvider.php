<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Middlewares\AllowSameOriginIframeInEditor;
use BagistoPlus\Visual\Middlewares\InjectThemeEditorScript;
use BagistoPlus\Visual\ThemeEditor;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ThemeEditor::class, fn () => new ThemeEditor);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/admin.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'visual');

        $this->bootMiddlewares();
    }

    protected function bootMiddlewares()
    {
        /** @var \Illuminate\Foundation\Http\Kernel */
        $kernel = $this->app[Kernel::class];

        $kernel->prependMiddleware(AllowSameOriginIframeInEditor::class);
        $kernel->pushMiddleware(InjectThemeEditorScript::class);
    }
}
