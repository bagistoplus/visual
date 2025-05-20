<?php

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Theme\Themes;
use Illuminate\Support\Facades\View;

it('should extends \\Webkul\\Theme\\Themes', function () {
    expect(get_parent_class(Themes::class))->toBe(\Webkul\Theme\Themes::class);
});

test('`app(\'themes\')` should resolve instance of \\BagistoPlus\\Visual\\Theme\Themes', function () {
    expect(themes())
        ->toBeInstanceOf(\BagistoPlus\Visual\Theme\Themes::class);
});

it('should load themes as instance of \\BagistoPlus\\Visual\\Theme\\Theme', function () {
    expect(themes()->all())
        ->toContainOnlyInstancesOf(\BagistoPlus\Visual\Theme\Theme::class);
});

it('should prepend default views path to shop namespace when active theme is visual theme', function () {
    ThemePathsResolver::shouldReceive('resolveThemeViewsPaths')
        ->andReturn([]);

    themes()->set('fake-theme');

    $defaultViewsPath = __DIR__.'/../../resources/views/theme';
    $shopPaths = View::getFinder()->getHints()['shop'];

    expect(array_map(fn ($path) => realpath($path), $shopPaths))
        ->toContain(realpath($defaultViewsPath));
});
