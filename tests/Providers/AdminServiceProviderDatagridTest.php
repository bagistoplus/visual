<?php

use BagistoPlus\Visual\Actions\Admin\PrepareCmsPageVisualDatagrid;
use BagistoPlus\Visual\Support\ChannelThemeResolver;
use BagistoPlus\Visual\Support\CmsPageVisualEditorUrlResolver;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Support\Facades\Route;
use Webkul\Core\Models\Channel;
use Webkul\DataGrid\DataGrid;

class CmsVisualActionTestDataGrid extends DataGrid
{
    public function prepareQueryBuilder() {}

    public function prepareColumns() {}
}

beforeEach(function () {
    config()->set('bagisto_visual.template_assignments', true);
    config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);

    Route::get('/cms/{url_key}', fn () => 'cms')->name('shop.cms.page');

    $this->app->bind(ChannelThemeResolver::class, fn () => new class extends ChannelThemeResolver
    {
        public function resolve(Channel|string|null $channel = null): ?Theme
        {
            return $this->resolveDefault();
        }

        public function resolveDefault(): ?Theme
        {
            return Theme::make([
                'code' => 'fake-theme',
                'name' => 'Fake Theme',
                'visual_theme' => true,
            ]);
        }
    });

    $this->app->bind(TemplateDiscovery::class, fn () => new class extends TemplateDiscovery
    {
        public function exists(
            Theme|string $theme,
            string $key,
            string $type,
            ?string $channel = null,
            ?string $locale = null,
            bool $includeEditorDrafts = false
        ): bool {
            return $key === 'page.landing'
                && $type === 'page'
                && $channel === 'default'
                && $locale === 'en'
                && $includeEditorDrafts;
        }
    });
});

it('does not touch the cms datagrid while template assignments are disabled', function () {
    config()->set('bagisto_visual.template_assignments', false);

    $datagrid = new CmsVisualActionTestDataGrid;

    app(PrepareCmsPageVisualDatagrid::class)->prepareActions($datagrid);

    expect($datagrid->getActions())->toBe([]);
});

function cmsVisualEditorUrlForTemplate(?string $template): ?string
{
    return app(CmsPageVisualEditorUrlResolver::class)
        ->forTemplate($template, 'about-us', 'en');
}

it('generates a cms visual editor url for valid custom page templates', function () {
    $url = cmsVisualEditorUrlForTemplate('page.landing');

    expect($url)->not->toBeNull()
        ->and($url)->toContain('/visual/editor/fake-theme')
        ->and($url)->toContain('template=page.landing')
        ->and($url)->toContain('previewUrl=')
        ->and(urldecode($url))->toContain('/cms/about-us')
        ->and($url)->toContain('channel=default')
        ->and($url)->toContain('locale=en');
});

it('does not generate a cms visual editor url without a valid custom page template', function (?string $templateKey) {
    expect(cmsVisualEditorUrlForTemplate($templateKey))->toBeNull();
})->with([
    'missing assignment' => null,
    'default template assignment' => 'page',
    'missing template file' => 'page.missing',
    'wrong type key' => 'product.landing',
]);

it('does not generate a cms visual editor url when the default channel has no visual theme', function () {
    $this->app->bind(ChannelThemeResolver::class, fn () => new class extends ChannelThemeResolver
    {
        public function resolveDefault(): ?Theme
        {
            return null;
        }
    });

    expect(cmsVisualEditorUrlForTemplate('page.landing'))->toBeNull();
});

it('adds the cms visual editor datagrid action metadata without executing a query', function () {
    $datagrid = new CmsVisualActionTestDataGrid;

    app(PrepareCmsPageVisualDatagrid::class)->prepareActions($datagrid);

    $action = $datagrid->getActions()[0]->toArray();
    $url = ($action['url'])((object) [
        'visual_template' => 'page.landing',
        'url_key' => 'about-us',
        'locale' => 'en',
    ]);

    expect($action['icon'])->toBe('icon-magic')
        ->and($action['title'])->toBe(__('visual::admin.cms.open-in-visual-editor'))
        ->and($action['method'])->toBe('GET')
        ->and($url)->toContain('template=page.landing');
});

it('does not add the cms visual editor datagrid action when there is no visual theme', function () {
    $this->app->bind(ChannelThemeResolver::class, fn () => new class extends ChannelThemeResolver
    {
        public function resolveDefault(): ?Theme
        {
            return null;
        }
    });

    $datagrid = new CmsVisualActionTestDataGrid;

    app(PrepareCmsPageVisualDatagrid::class)->prepareActions($datagrid);

    expect($datagrid->getActions())->toBe([]);
});
