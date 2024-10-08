<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Middlewares\UseShopThemeFromRequest;
use BagistoPlus\Visual\Sections\AnnouncementBar;
use BagistoPlus\Visual\Sections\SectionRepository;
use BagistoPlus\Visual\ThemeDataCollector;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'visual');

        Visual::registerSection(AnnouncementBar::class, 'visual');

        $this->bootMiddlewares();

        if ($this->app->runningInConsole()) {
            $this->publishAssets();
        }
    }

    public function register()
    {
        $this->registerConfigs();

        $this->app->singleton(SectionRepository::class, fn () => new SectionRepository);

        $this->app->singleton(ThemeDataCollector::class, fn () => new ThemeDataCollector($this->app->get('files')));
    }

    protected function registerConfigs()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/bagisto-visual.php', 'bagisto_visual');
    }

    protected function publishAssets()
    {
        $this->publishes([
            __DIR__.'/../../public' => public_path('vendor/bagistoplus/visual'),
        ], ['public', 'bagistoplus-visual-assets']);
    }

    protected function bootMiddlewares()
    {
        $this->app->booted(function () {
            $router = $this->app[Router::class];
            $router->aliasMiddleware('theme', UseShopThemeFromRequest::class);
        });
    }
}
