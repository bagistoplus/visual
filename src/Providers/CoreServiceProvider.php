<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Commands;
use BagistoPlus\Visual\Components\Svg;
use BagistoPlus\Visual\Data\BlockData;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Middlewares\DisableResponseCacheInDesignMode;
use BagistoPlus\Visual\Middlewares\UseShopThemeFromRequest;
use BagistoPlus\Visual\Settings\Support as SettingTransformers;
use BagistoPlus\Visual\Support\BlockRenderFilter;
use BagistoPlus\Visual\Support\UrlGenerator;
use BagistoPlus\Visual\TemplateRegistrar;
use BagistoPlus\Visual\ThemePathsResolver;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\Events\BlockSchemaRegistered;
use Craftile\Laravel\Events\JsonViewLoaded;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\DynamicComponent;

class CoreServiceProvider extends ServiceProvider
{
    protected static $commands = [
        Commands\MakeThemeCommand::class,
        Commands\MakeSectionCommand::class,
        Commands\MakeBlockCommand::class,
        Commands\GeneratePreviewCommand::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootCraftile();
        $this->bootShopRoutes();
        $this->bootViewsAndTranslations();
        $this->bootMiddlewares();
        $this->bootVisualSections();
        $this->bootBladeIcons();
        $this->bootMorphMap();

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

    protected function bootCraftile(): void
    {
        config([
            'craftile.directives' => [
                'craftileBlock' => 'visualBlock',
                'craftileRegion' => 'visualRegion',
                'craftileContent' => 'visualContent',
                'craftileLayoutContent' => 'visualLayoutContent',
            ],

            'craftile.components.namespace' => 'visual',

            'craftile.block_data_class' => \BagistoPlus\Visual\Data\BlockData::class,
            'craftile.block_schema_class' => \BagistoPlus\Visual\Data\BlockSchema::class,

            'craftile.php_template_extensions' => ['visual.php'],
        ]);

        Craftile::resolveRegionViewUsing(function ($name) {
            return "shop::regions.$name";
        });

        Craftile::detectPreviewUsing(function () {
            return ThemeEditor::inDesignMode();
        });

        Craftile::normalizeTemplateUsing(new \BagistoPlus\Visual\Support\TemplateNormalizer);

        Craftile::checkIfBlockCanRenderUsing(function (BlockData $blockData) {
            if (! ThemeEditor::inDesignMode() || ! request()->has('_vkey')) {
                return ! $blockData->disabled;
            }

            $filter = app(BlockRenderFilter::class);

            return $filter->shouldRender($blockData);
        });

        $this->registerPropertyTransformers();
        $this->registerBlockCompilers();
    }

    protected function registerPropertyTransformers(): void
    {
        $transformers = [
            'icon' => SettingTransformers\IconTransformer::class,
            'link' => SettingTransformers\LinkTransformer::class,
            'font' => SettingTransformers\FontTransformer::class,
            'color' => SettingTransformers\ColorTransformer::class,
            'image' => SettingTransformers\ImageTransformer::class,
            'product' => SettingTransformers\ProductTransformer::class,
            'category' => SettingTransformers\CategoryTransformer::class,
            'cms-page' => SettingTransformers\CmsPageTransformer::class,
            'rich-text' => SettingTransformers\RichTextTransformer::class,
            'color-scheme' => SettingTransformers\ColorSchemeTransformer::class,
            'color-scheme-group' => SettingTransformers\ColorSchemeGroupTransformer::class,
            'gradient' => SettingTransformers\GradientTransformer::class,
        ];

        foreach ($transformers as $type => $transformerClass) {
            Craftile::registerPropertyTransformer($type, new $transformerClass);
        }
    }

    protected function registerBlockCompilers(): void
    {
        if (class_exists(\Livewire\Component::class)) {
            $registry = app(\Craftile\Laravel\View\BlockCompilerRegistry::class);
            $registry->register(new \BagistoPlus\Visual\View\Compilers\LivewireBlockCompiler);

            // Register Livewire blocks as Livewire components when they're discovered
            Event::listen(BlockSchemaRegistered::class, function (BlockSchemaRegistered $event) {
                if ($event->schema->class && is_subclass_of($event->schema->class, \Livewire\Component::class)) {
                    $componentName = 'craftile-'.$event->schema->slug;
                    \Livewire\Livewire::component($componentName, $event->schema->class);
                }
            });
        }
    }

    protected function bootShopRoutes(): void
    {
        Route::prefix('/visual/template-preview')
            ->middleware(['web', 'locale', 'theme', 'currency'])
            ->group(__DIR__.'/../../routes/shop.php');
    }

    protected function bootViewsAndTranslations(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'visual');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'visual');
    }

    protected function bootMiddlewares(): void
    {
        $this->app->bind(\Webkul\Shop\Http\Middleware\Theme::class, UseShopThemeFromRequest::class);

        $this->app[\Illuminate\Contracts\Http\Kernel::class]
            ->prependMiddleware(DisableResponseCacheInDesignMode::class);
    }

    protected function bootVisualSections(): void
    {
        $this->app->booted(function () {
            Visual::discoverSectionsIn(app_path(('Visual/Sections')), 'App\\Visual\\Sections');
            Visual::discoverBlocksIn(app_path(('Visual/Blocks')), 'App\\Visual\\Blocks');
        });
    }

    protected function bootBladeIcons()
    {
        Blade::component('dynamic-component', DynamicComponent::class);

        // Register alias for some blade-icons icons
        foreach (config('bagisto_visual_iconmap') as $alias => $icon) {
            Blade::component(Svg::class, $alias);
        }
    }

    protected function bootMorphMap(): void
    {
        Relation::morphMap([
            'product' => \Webkul\Product\Models\Product::class,
            'category' => \Webkul\Category\Models\Category::class,
            'attribute' => \Webkul\Attribute\Models\Attribute::class,
        ]);
    }

    protected function bootCommands(): void
    {
        $this->commands(static::$commands);
    }

    protected function bootPublishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../../public/vendor/bagistoplus' => public_path('vendor/bagistoplus'),
        ], ['public', 'visual', 'visual-assets']);

        $this->publishes([
            __DIR__.'/../../config/bagisto-visual.php' => config_path('bagisto_visual.php'),
        ], ['config', 'visual', 'visual-config']);
    }

    protected function bootTemplates(): void
    {

        if (ThemeEditor::active()) {
            Event::listen(JsonViewLoaded::class, function (JsonViewLoaded $event) {
                ThemeEditor::addJsonView($event->path);
            });

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
        $this->app->singleton(ThemeSettingsLoader::class, function (Application $app) {
            return new ThemeSettingsLoader(
                $app->get(ThemePathsResolver::class),
                $app->get('files')
            );
        });

        // Register BlockRenderFilter as singleton for request-scoped caching
        $this->app->singleton(BlockRenderFilter::class);
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
