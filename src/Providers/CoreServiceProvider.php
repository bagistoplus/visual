<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Sections\AnnouncementBar;
use BagistoPlus\Visual\Sections\SectionRepository;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'visual');

        Visual::registerSection(AnnouncementBar::class, 'visual');

        $this->publishAssets();
    }

    public function register()
    {
        $this->app->singleton(SectionRepository::class, fn () => new SectionRepository);
    }

    protected function publishAssets()
    {
        $this->publishes([
            __DIR__.'/../../public' => public_path('vendor/bagistoplus/visual'),
        ], ['public', 'bagistoplus-visual-assets']);
    }
}
