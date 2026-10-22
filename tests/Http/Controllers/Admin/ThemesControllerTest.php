<?php

use BagistoPlus\Visual\Http\Controllers\Admin\ThemesController;
use Illuminate\Support\Collection;

function listedThemeCodes(): array
{
    return app(ThemesController::class)
        ->index()
        ->getData()['themes']
        ->pluck('code')
        ->all();
}

beforeEach(function () {
    config()->set('themes.shop', [
        'default' => ['name' => 'Default'],
        'visual-demo' => ['code' => 'visual-demo', 'name' => 'Visual Demo', 'visual_theme' => true],
    ]);

    fakeThemeCatalog([]);
});

it('lists a plain theme alongside visual ones when bagisto stores sections', function () {
    fakeSectionSystem(available: true);

    expect(listedThemeCodes())->toBe(['default', 'visual-demo']);
});

it('leaves plain themes out when bagisto has no section system', function () {
    fakeSectionSystem(available: false);

    expect(listedThemeCodes())->toBe(['visual-demo']);
});

function listedTheme(string $code): array
{
    return app(ThemesController::class)
        ->index()
        ->getData()['themes']
        ->firstWhere('code', $code);
}

function fakeThemeCatalog(array $entries): void
{
    app()->instance('Webkul\Theme\ThemeCatalog', new class($entries)
    {
        public function __construct(protected array $entries) {}

        public function all(): Collection
        {
            return collect($this->entries);
        }
    });
}

it('describes a theme with what its config declares', function () {
    fakeSectionSystem(available: true);
    fakeThemeCatalog([]);

    config()->set('themes.shop.visual-demo', [
        'name' => 'Visual Demo',
        'visual_theme' => true,
        'author' => 'Acme',
        'version' => '2.1.0',
        'description' => 'A demo theme.',
        'preview_image' => 'themes/visual-demo/preview.png',
    ]);

    expect(listedTheme('visual-demo'))->toMatchArray([
        'author' => 'Acme',
        'version' => '2.1.0',
        'description' => 'A demo theme.',
        'screenshot' => asset('themes/visual-demo/preview.png'),
    ]);
});

it('takes what a theme config leaves out from bagisto theme catalog', function () {
    fakeSectionSystem(available: true);
    fakeThemeCatalog([
        ['code' => 'default', 'author' => 'Webkul', 'version' => '2.5', 'description' => 'The default theme.'],
    ]);

    expect(listedTheme('default'))->toMatchArray([
        'author' => 'Webkul',
        'version' => '2.5',
        'description' => 'The default theme.',
    ]);
})->skip(
    fn () => ! class_exists('Webkul\Theme\ThemeCatalog'),
    'bagisto has no theme catalog'
);

it('shows the screenshot bagisto theme catalog has for a theme declaring none', function () {
    fakeSectionSystem(available: true);
    fakeThemeCatalog([
        ['code' => 'default', 'screenshot' => 'http://shop.test/themes/admin/default/build/assets/default.png'],
    ]);

    expect(listedTheme('default')['screenshot'])
        ->toBe('http://shop.test/themes/admin/default/build/assets/default.png');
})->skip(
    fn () => ! class_exists('Webkul\Theme\ThemeCatalog'),
    'bagisto has no theme catalog'
);

it('prefers the preview a theme config declares over the catalog screenshot', function () {
    fakeSectionSystem(available: true);
    fakeThemeCatalog([
        ['code' => 'visual-demo', 'screenshot' => 'http://shop.test/catalog.png'],
    ]);

    config()->set('themes.shop.visual-demo.preview_image', 'themes/visual-demo/preview.png');

    expect(listedTheme('visual-demo')['screenshot'])->toBe(asset('themes/visual-demo/preview.png'));
});

it('shows no version a theme never declared', function () {
    fakeSectionSystem(available: true);
    fakeThemeCatalog([]);

    expect(listedTheme('visual-demo')['version'])->toBeNull();
});

it('leaves the screenshot to the view when a theme declares none', function () {
    fakeSectionSystem(available: true);
    fakeThemeCatalog([]);

    expect(listedTheme('visual-demo')['screenshot'])->toBeNull();
});
