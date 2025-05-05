<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Commands;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\LivewireFeatures\SectionDataSynth;
use BagistoPlus\Visual\Middlewares\UseShopThemeFromRequest;
use BagistoPlus\Visual\Sections\SectionRepository;
use BagistoPlus\Visual\Support\UrlGenerator;
use BagistoPlus\Visual\TemplateRegistrar;
use BagistoPlus\Visual\ThemeDataCollector;
use BagistoPlus\Visual\ThemePathsResolver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\DynamicComponent;
use Livewire\Livewire;
use Webkul\Shop\Http\Middleware\Currency;
use Webkul\Shop\Http\Middleware\Locale;
use Webkul\Theme\ViewRenderEventManager;

class CoreServiceProvider extends ServiceProvider
{
    protected static $commands = [
        Commands\MakeThemeCommand::class,
        Commands\MakeSectionCommand::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootViewsAndTranslations();
        $this->bootViewEventListeners();
        $this->bootMiddlewares();
        $this->bootLivewireMiddlewares();
        $this->bootVisualSections();
        $this->bootBladeIcons();
        $this->bootMorphMap();
        $this->bootLivewirePropertySynthesizer();

        $this->app->booted(function (Application $app) {
            if (! $app->runningInConsole()) {
                Route::getRoutes()->refreshNameLookups();
                $this->bootTemplates();
            }
        });

        if ($this->app->runningInConsole()) {
            $this->bootCommands();
            $this->bootPublishAssets();
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerConfigs();
        $this->registerSingletons();
        $this->registerCustomUrlGenerator();
    }

    /*
     * ---------------------------------------------------------
     * Boot Methods
     * ---------------------------------------------------------
     */

    protected function bootViewsAndTranslations(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'visual');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'visual');
    }

    protected function bootViewEventListeners()
    {
        Event::listen('bagisto.shop.checkout.payment.paypal_smart_button', function (ViewRenderEventManager $event) {
            if (app('themes')->current()->code !== 'visual-debut') {
                return;
            }

            $event->addTemplate('paypal::checkout.onepage.payment-button');
        });
    }

    protected function bootMiddlewares(): void
    {
        // $this->app->booted(function ($app) {
        $this->app->bind(\Webkul\Shop\Http\Middleware\Theme::class, UseShopThemeFromRequest::class);
        // });
    }

    protected function bootLivewireMiddlewares(): void
    {
        Livewire::addPersistentMiddleware([
            Locale::class,
            Currency::class,
            \Webkul\Shop\Http\Middleware\Theme::class,
        ]);
    }

    protected function bootVisualSections(): void
    {
        Visual::discoverSectionsIn(base_path(('Visual/Sections')));
    }

    protected function bootBladeIcons()
    {
        $icons = [
            'icon-users' => 'lucide-chevron-right ',
        ];

        Blade::component('dynamic-component', DynamicComponent::class);

        $this->app->booted(function () use ($icons) {
            foreach ($icons as $alias => $icon) {
                Blade::component($icon, $alias);
            }
        });
    }

    protected function bootMorphMap(): void
    {
        Relation::morphMap([
            'product' => \Webkul\Product\Models\Product::class,
            'category' => \Webkul\Category\Models\Category::class,
            'attribute' => \Webkul\Attribute\Models\Attribute::class,
        ]);
    }

    protected function bootLivewirePropertySynthesizer(): void
    {
        Livewire::propertySynthesizer(SectionDataSynth::class);
    }

    protected function bootCommands(): void
    {
        $this->commands(static::$commands);
    }

    protected function bootPublishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../../public/vendor/bagistoplus' => public_path('vendor/bagistoplus'),
        ], ['public', 'bagistoplus-visual-assets']);
    }

    protected function bootTemplates(): void
    {
        if (ThemeEditor::inDesignMode()) {
            $this->app->make(TemplateRegistrar::class)->registerTemplates();
        }
    }

    /*
     * ---------------------------------------------------------
     * Register Methods
     * ---------------------------------------------------------
     */

    protected function registerConfigs(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/bagisto-visual.php', 'bagisto_visual');
        $this->mergeConfigFrom(__DIR__.'/../../config/svg-iconmap.php', 'bagisto_visual_iconmap');
    }

    protected function registerSingletons(): void
    {
        $this->app->singleton(SectionRepository::class, fn () => new SectionRepository);

        $this->app->singleton(ThemeDataCollector::class, function (Application $app) {
            return new ThemeDataCollector(
                $app->get(ThemePathsResolver::class),
                $app->get('files')
            );
        });
    }

    protected function registerCustomUrlGenerator(): void
    {
        $this->app->bind('url', function ($app) {
            $routes = $app['router']->getRoutes();

            return new UrlGenerator(
                $routes,
                $app->rebinding('request', fn ($app, $request) => $app['url']->setRequest($request)),
                $app['config']['app.asset_url']
            );
        });
    }
}
