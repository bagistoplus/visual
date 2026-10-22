<?php

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\Theme\Themes;
use Illuminate\Support\Facades\View;

it('should extends \\Webkul\\Theme\\Themes', function () {
    expect(get_parent_class(Themes::class))->toBe(Webkul\Theme\Themes::class);
});

test('`themes()` should resolve instance of \\BagistoPlus\\Visual\\Theme\Themes', function () {
    expect(themes())
        ->toBeInstanceOf(Themes::class);
});

it('should load themes as instance of \\BagistoPlus\\Visual\\Theme\\Theme', function () {
    expect(themes()->all())
        ->toContainOnlyInstancesOf(Theme::class);
});

it('should prepend default views path to shop namespace when active theme is visual theme', function () {
    ThemePathsResolver::shouldReceive('resolveThemeViewsPaths')
        ->andReturn([]);

    themes()->set('fake-theme');

    $defaultViewsPath = __DIR__.'/../../resources/views/theme';
    $shopPaths = View::getFinder()->getHints()['shop'];

    expect(array_map(fn ($path) => realpath($path), $shopPaths))
        ->toContain(realpath($defaultViewsPath));
})->skip('Requires full Bagisto bootstrap with request context');

it('draws a bagisto theme\'s sections through the views their types name', function () {
    fakeSectionSystem(available: true);

    config()->set('app.admin_url', 'admin');
    config()->set('themes.shop.default', ['name' => 'Default']);

    $themes = new Themes;
    $themes->loadThemes();
    $themes->set('default');

    $hints = app('view')->getFinder()->getHints()['shop'] ?? [];

    expect(collect($hints)->contains(fn (string $hint) => str_ends_with($hint, 'resources/views/bagisto')))
        ->toBeTrue();
});

it('leaves a theme alone when bagisto does not declare its section types', function () {
    fakeSectionSystem(available: false);

    config()->set('app.admin_url', 'admin');
    config()->set('themes.shop.default', ['name' => 'Default']);

    $before = app('view')->getFinder()->getHints()['shop'] ?? [];

    $themes = new Themes;
    $themes->loadThemes();
    $themes->set('default');

    expect(app('view')->getFinder()->getHints()['shop'] ?? [])->toBe($before)
        ->and(collect($before)->contains(fn (string $hint) => str_ends_with($hint, 'resources/views/bagisto')))
        ->toBeFalse();
});
