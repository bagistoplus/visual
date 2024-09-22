<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Theme\Themes;
use BagistoPlus\Visual\View\VisualTagsCompiler;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerVisualTagsCompiler();

        $this->app->singleton('themes', fn () => new Themes);
    }

    protected function registerVisualTagsCompiler()
    {
        $this->app['blade.compiler']->precompiler(app(VisualTagsCompiler::class));
    }
}
