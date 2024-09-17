<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Theme\Themes;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'visual');

        $this->publishAssets();
        $this->app->singleton('themes', fn () => new Themes);
    }

    protected function publishAssets()
    {
        $this->publishes([
            __DIR__.'/../../public' => public_path('vendor/bagistoplus/visual'),
        ], ['public', 'bagistoplus-visual-assets']);
    }
}
