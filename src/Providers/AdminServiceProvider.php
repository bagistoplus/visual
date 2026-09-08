<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Actions\Admin\AddCmsPageEditVisualEditorButton;
use BagistoPlus\Visual\Actions\Admin\AddTemplateAssignmentField;
use BagistoPlus\Visual\Actions\Admin\EnrichVisualThemesInThemeGallery;
use BagistoPlus\Visual\Actions\Admin\PersistTemplateAssignment;
use BagistoPlus\Visual\Actions\Admin\PrepareCmsPageVisualDatagrid;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Middlewares\AllowSameOriginIframeInEditor;
use BagistoPlus\Visual\Middlewares\DispatchServingThemeEditor;
use BagistoPlus\Visual\Middlewares\RedirectVisualThemeCustomization;
use BagistoPlus\Visual\ThemeEditor;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerConfig();

        $this->app->singleton(ThemeEditor::class, fn () => new ThemeEditor);
    }

    public function boot()
    {
        $this->bootRoutes();
        $this->bootMiddlewares();
        $this->bootInputNormalizationExceptions();
        $this->bootViewComposers();
        $this->bootViewEventListeners();

        if (Visual::templateAssignmentsEnabled()) {
            app(PrepareCmsPageVisualDatagrid::class)->listen();
        }
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/admin-menu.php', 'menu.admin');
        $this->mergeConfigFrom(__DIR__.'/../../config/acl.php', 'acl');
        $this->mergeConfigFrom(__DIR__.'/../../config/viters.php', 'bagisto-vite.viters');
    }

    protected function bootRoutes(): void
    {
        Route::prefix(config('app.admin_url'))
            ->middleware(['web', 'admin'])
            ->group(__DIR__.'/../../routes/admin.php');
    }

    protected function bootMiddlewares()
    {
        /** @var \Illuminate\Foundation\Http\Kernel */
        $kernel = $this->app->get(Kernel::class);

        $kernel->prependMiddleware(AllowSameOriginIframeInEditor::class);
        $kernel->pushMiddleware(DispatchServingThemeEditor::class);
        $kernel->pushMiddleware(RedirectVisualThemeCustomization::class);
    }

    protected function bootInputNormalizationExceptions(): void
    {
        $adminPrefix = trim((string) config('app.admin_url'), '/');
        $editorApiPrefix = ($adminPrefix === '' ? '' : $adminPrefix.'/').'visual/editor/api';

        $shouldPreserveEditorInput = fn (Request $request): bool => $request->is(
            $editorApiPrefix.'/persist-updates',
            $editorApiPrefix.'/persist-settings',
            $editorApiPrefix.'/publish-theme',
        );

        TrimStrings::skipWhen($shouldPreserveEditorInput);
        ConvertEmptyStringsToNull::skipWhen($shouldPreserveEditorInput);
    }

    protected function bootViewComposers(): void
    {
        View::composer('admin::appearance.themes.index', function ($view) {
            app(EnrichVisualThemesInThemeGallery::class)($view);
        });
    }

    protected function bootViewEventListeners()
    {
        Event::listen('bagisto.admin.layout.head.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('visual::admin.layouts.style');
        });

        Event::listen('bagisto.admin.layout.body.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('visual::admin.layouts.datagrid-action-title');
        });

        Event::listen('bagisto.admin.catalog.product.edit.form.price.before', function ($viewRenderEventManager) {
            app(AddTemplateAssignmentField::class)($viewRenderEventManager, 'product');
        });

        Event::listen('bagisto.admin.catalog.categories.edit.card.accordion.settings.after', function ($viewRenderEventManager) {
            app(AddTemplateAssignmentField::class)($viewRenderEventManager, 'category');
        });

        Event::listen('bagisto.admin.cms.pages.edit.card.accordion.seo.after', function ($viewRenderEventManager) {
            app(AddTemplateAssignmentField::class)($viewRenderEventManager, 'page');
        });

        Event::listen('bagisto.admin.cms.pages.edit.create_form_controls.before', function ($viewRenderEventManager) {
            app(AddCmsPageEditVisualEditorButton::class)($viewRenderEventManager);
        });

        if (! Visual::templateAssignmentsEnabled()) {
            return;
        }

        Event::listen('catalog.product.update.after', fn ($product) => app(PersistTemplateAssignment::class)($product, 'product'));
        Event::listen('catalog.category.update.after', fn ($category) => app(PersistTemplateAssignment::class)($category, 'category'));
        Event::listen('cms.page.update.after', fn ($page) => app(PersistTemplateAssignment::class)($page, 'page'));
    }
}
