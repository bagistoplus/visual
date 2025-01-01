<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Middlewares\UseShopThemeFromRequest;
use BagistoPlus\Visual\Sections\AnnouncementBar;
use BagistoPlus\Visual\Sections\LiveCounter;
use BagistoPlus\Visual\Sections\SectionRepository;
use BagistoPlus\Visual\Support\Template;
use BagistoPlus\Visual\Support\UrlGenerator;
use BagistoPlus\Visual\ThemeDataCollector;
use BagistoPlus\Visual\ThemePathsResolver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\CMS\Repositories\PageRepository;
use Webkul\Installer\Http\Middleware\Locale;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Middleware\Currency;

class CoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'visual');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'visual');

        $this->bootMiddlewares();
        $this->bootMiddlewaresForLivewire();

        Visual::registerSection(AnnouncementBar::class, 'visual');
        Visual::registerSection(LiveCounter::class, 'visual');

        $this->app->booted(function (Application $app) {
            if (! $app->runningInConsole()) {
                Route::getRoutes()->refreshNameLookups();
                $this->registerTemplates();
            }
        });

        if ($this->app->runningInConsole()) {
            $this->publishAssets();
        }
    }

    public function register()
    {
        $this->registerConfigs();

        $this->app->singleton(SectionRepository::class, fn () => new SectionRepository);
        $this->app->singleton(ThemeDataCollector::class, function (Application $app) {
            return new ThemeDataCollector(
                $app->get(ThemePathsResolver::class),
                $app->get('files')
            );
        });

        $this->registerCustomUrlGenerator();
    }

    protected function registerConfigs()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/bagisto-visual.php', 'bagisto_visual');
    }

    protected function registerCustomUrlGenerator()
    {
        $this->app->bind('url', function ($app) {
            $routes = $app['router']->getRoutes();

            return new UrlGenerator(
                $routes,
                $app->rebinding('request', function ($app, $request) {
                    $app['url']->setRequest($request);
                }),
                $app['config']['app.asset_url']
            );
        });
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

    public function bootMiddlewaresForLivewire()
    {
        Livewire::addPersistentMiddleware([
            Locale::class,
            Currency::class,
            UseShopThemeFromRequest::class,
        ]);
    }

    protected function registerTemplates()
    {
        $templates = [
            new Template(
                template: 'index',
                route: 'shop.home.index',
                label: 'Home page',
                icon: 'heroicon-o-home',
                previewUrl: url()->to('/')
            ),
        ];

        // add category template if any category exists

        $category = app(CategoryRepository::class)
            ->where('parent_id', app('core')->getCurrentChannel()->root_category_id)
            ->first();

        if ($category !== null) {
            $templates[] = new Template(
                template: 'category',
                route: 'shop.categories.index',
                label: 'Category Page',
                icon: 'fluentui-tag-multiple-24-o',
                previewUrl: $category->url,
            );
        }

        // add product template if any product exists

        $product = app(ProductRepository::class)->first();

        if ($product !== null && $product->url_key) {
            $templates[] = new Template(
                template: 'product',
                route: 'shop.products.index',
                label: 'Product Page',
                icon: 'fluentui-tag-24-o',
                previewUrl: $category->url,
            );
        }

        $templates[] = Template::separator();

        $templates[] = new Template(
            template: 'cart',
            route: 'shop.checkout.cart.index',
            label: 'Cart Page',
            icon: 'fluentui-cart-24-o',
            previewUrl: route('shop.checkout.cart.index')
        );

        $templates[] = new Template(
            template: 'checkout',
            route: 'shop.checkout.onepage.index',
            label: 'Checkout Page',
            icon: 'fluentui-cart-24-o',
            previewUrl: route('shop.checkout.onepage.index')
        );

        $templates[] = new Template(
            template: 'checkout-success',
            route: 'shop.checkout.onepage.success',
            label: 'Checkout success',
            icon: 'fluentui-cart-24-o',
            previewUrl: route('shop.checkout.onepage.success')
        );

        $templates[] = Template::separator();

        $templates[] = new Template(
            template: 'search',
            route: 'shop.search.index',
            label: 'Search results',
            icon: 'heroicon-o-magnifying-glass',
            previewUrl: route('shop.search.index')
        );

        // add cms page template if there is any page

        $page = app(PageRepository::class)->first();

        if ($page !== null) {
            $templates[] = new Template(
                template: 'page',
                route: 'shop.cms.page',
                label: 'CMS Page',
                icon: 'heroicon-o-newspaper',
                previewUrl: route('shop.cms.page', [$page->url_key])
            );
        }

        $templates[] = new Template(
            template: 'contact',
            route: 'shop.home.contact_us',
            label: 'Contact us ',
            icon: 'heroicon-o-phone',
            previewUrl: route('shop.home.contact_us')
        );

        foreach ($templates as $template) {
            ThemeEditor::registerTemplate($template);
        }
    }
}
