<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Components\AddToCartButton;
use BagistoPlus\Visual\Components\CartCouponForm;
use BagistoPlus\Visual\Components\CartPreview;
use BagistoPlus\Visual\Components\EstimateShipping;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\LivewireFeatures\SectionDataSynth;
use BagistoPlus\Visual\Middlewares\UseShopThemeFromRequest;
use BagistoPlus\Visual\Sections;
use BagistoPlus\Visual\Sections\SectionRepository;
use BagistoPlus\Visual\Support\UrlGenerator;
use BagistoPlus\Visual\TemplateRegistrar;
use BagistoPlus\Visual\ThemeDataCollector;
use BagistoPlus\Visual\ThemePathsResolver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Webkul\Category\Models\Category;
use Webkul\Installer\Http\Middleware\Locale;
use Webkul\Product\Models\Product;
use Webkul\Shop\Http\Middleware\Currency;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * The list of sections to be registered.
     */
    protected static array $sections = [
        Sections\AnnouncementBar::class,
        Sections\Header::class,
        Sections\Footer::class,
        Sections\Hero::class,
        Sections\CategoryList::class,
        Sections\FeaturedProducts::class,
        Sections\Newsletter::class,
        Sections\Breadcrumbs::class,
        Sections\CategoryPage::class,
        Sections\ProductDetails::class,
        Sections\CartContent::class,
        Sections\Checkout::class,
        Sections\LoginForm::class,
        Sections\RegisterForm::class,
    ];

    /**
     * The list of Livewire components to be registered.
     */
    protected static array $livewireComponents = [
        'cart-preview' => CartPreview::class,
        'cart-coupon-form' => CartCouponForm::class,
        'add-to-cart-button' => AddToCartButton::class,
        'estimate-shipping' => EstimateShipping::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootViewsAndTranslations();
        $this->bootBladeComponents();
        $this->bootMiddlewares();
        $this->bootLivewireMiddlewares();
        $this->bootLivewireComponents();
        $this->bootVisualSections();
        $this->bootMorphMap();
        $this->bootLivewirePropertySynthesizer();

        $this->app->booted(function (Application $app) {
            $this->bootPaginationViews();

            if (! $app->runningInConsole()) {
                Route::getRoutes()->refreshNameLookups();
                $this->bootTemplates();
            }
        });

        if ($this->app->runningInConsole()) {
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

    protected function bootPaginationViews(): void
    {
        Paginator::defaultView('shop::pagination.default');
        Paginator::defaultSimpleView('shop::pagination.default');
    }

    protected function bootBladeComponents(): void
    {
        Blade::componentNamespace('BagistoPlus\\Visual\\Components', 'visual');
    }

    protected function bootMiddlewares(): void
    {
        $this->app->booted(function () {
            $this->app->make(Router::class)
                ->aliasMiddleware('theme', UseShopThemeFromRequest::class);
        });
    }

    protected function bootLivewireMiddlewares(): void
    {
        Livewire::addPersistentMiddleware([
            Locale::class,
            Currency::class,
            UseShopThemeFromRequest::class,
        ]);
    }

    protected function bootLivewireComponents(): void
    {
        foreach (self::$livewireComponents as $name => $component) {
            Livewire::component($name, $component);
        }
    }

    protected function bootVisualSections(): void
    {
        Visual::registerSections(static::$sections, 'visual');
    }

    protected function bootMorphMap(): void
    {
        Relation::morphMap([
            'product' => Product::class,
            'category' => Category::class,
        ]);
    }

    protected function bootLivewirePropertySynthesizer(): void
    {
        Livewire::propertySynthesizer(SectionDataSynth::class);
    }

    protected function bootPublishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../../public' => public_path('vendor/bagistoplus/visual'),
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
