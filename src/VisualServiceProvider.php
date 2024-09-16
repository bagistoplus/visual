<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Theme\Themes;
use Illuminate\Support\ServiceProvider;

class VisualServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('themes', fn () => new Themes);
    }
}
