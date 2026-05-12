<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Commands;
use BagistoPlus\Visual\Components\Svg;
use BagistoPlus\Visual\Data\BlockData;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Middlewares\DisableResponseCacheInDesignMode;
use BagistoPlus\Visual\Middlewares\UseShopThemeFromRequest;
use BagistoPlus\Visual\Models\VisualTemplateAssignment;
use BagistoPlus\Visual\Settings\Support as SettingTransformers;
use BagistoPlus\Visual\Support\BlockRenderFilter;
use BagistoPlus\Visual\Support\ChannelThemeResolver;
use BagistoPlus\Visual\Support\TemplateAssignment;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Support\TemplateNormalizer;
use BagistoPlus\Visual\Support\UrlGenerator;
use BagistoPlus\Visual\TemplateRegistrar;
use BagistoPlus\Visual\ThemePathsResolver;
use BagistoPlus\Visual\ThemeSettingsLoader;
use BagistoPlus\Visual\View\Compilers\LivewireBlockCompiler;
use Craftile\Laravel\Events\JsonViewLoaded;
use Craftile\Laravel\Facades\Craftile;
use Craftile\Laravel\View\BlockCompilerRegistry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\DynamicComponent;
use Livewire\Component;
use Webkul\Attribute\Models\Attribute;
use Webkul\Category\Models\Category;
use Webkul\CMS\Models\Page;
use Webkul\Core\Models\Channel;
use Webkul\Product\Models\Product;
use Webkul\Shop\Http\Middleware\Theme;
use Webkul\Theme\Models\ThemeCustomization;

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
        $this->registerCraftileConfig();
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
        Craftile::resolveRegionViewUsing(function ($name) {
            return "shop::regions.$name";
        });

        Craftile::detectPreviewUsing(function () {
            return ThemeEditor::inDesignMode();
        });

        Craftile::normalizeTemplateUsing(new TemplateNormalizer);

        Craftile::checkIfBlockCanRenderUsing(function (BlockData $blockData) {
            if ($blockData->disabled) {
                return false;
            }

            if (ThemeEditor::inDesignMode() && request()->has('_vkey')) {
                $filter = app(BlockRenderFilter::class);

                return $filter->shouldRender($blockData);
            }

            return true;
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
            'spacing' => SettingTransformers\SpacingTransformer::class,
            'typography_presets' => SettingTransformers\TypographyPresetsTransformer::class,
            'typography' => SettingTransformers\TypographyTransformer::class,
        ];

        foreach ($transformers as $type => $transformerClass) {
            Craftile::registerPropertyTransformer($type, new $transformerClass);
        }
    }

    protected function registerBlockCompilers(): void
    {
        if (class_exists(Component::class)) {
            $registry = app(BlockCompilerRegistry::class);
            $registry->register(new LivewireBlockCompiler);
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
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    protected function bootMiddlewares(): void
    {
        $this->app->bind(Theme::class, UseShopThemeFromRequest::class);

        /** @var Kernel $kernel */
        $kernel = $this->app->make(HttpKernelContract::class);
        $kernel->prependMiddleware(DisableResponseCacheInDesignMode::class);
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
            'product' => Product::class,
            'category' => Category::class,
            'page' => Page::class,
            'attribute' => Attribute::class,
            'theme' => ThemeCustomization::class,
            'channel' => Channel::class,
            'visualtpl' => VisualTemplateAssignment::class,
        ]);

        foreach ([Product::class, Category::class, Page::class] as $model) {
            $model::resolveRelationUsing(
                'visualTemplateAssignments',
                fn ($model) => $model->morphMany(VisualTemplateAssignment::class, 'assignable')
            );

            $model::addGlobalScope(
                'visual_template_assignments',
                fn (Builder $builder) => $builder->with('visualTemplateAssignments')
            );
        }
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

    protected function registerCraftileConfig(): void
    {
        config([
            'craftile.directives' => [
                'craftileBlock' => 'visualBlock',
                'craftileRegion' => 'visualRegion',
                'craftileContent' => 'visualContent',
                'craftileLayoutContent' => 'visualLayoutContent',
            ],

            'craftile.components.namespace' => 'visual',

            'craftile.block_data_class' => BlockData::class,
            'craftile.block_schema_class' => BlockSchema::class,

            'craftile.php_template_extensions' => ['visual.php'],
        ]);
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
        $this->app->singleton(TemplateDiscovery::class);
        $this->app->singleton(ChannelThemeResolver::class);
        $this->app->singleton(TemplateAssignment::class);
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
