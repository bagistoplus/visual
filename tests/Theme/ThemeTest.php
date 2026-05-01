<?php

use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\PropertyBag;

afterEach(function () {
    Mockery::close();
});

it('should extends \\Webkul\\Theme\\Theme', function () {
    expect(get_parent_class(Theme::class))
        ->toBeClass(Webkul\Theme\Theme::class);
});

it('lazily loads settings on first access without throwing uninitialized property error', function () {
    $expected = new PropertyBag(['lazy' => 'loaded']);

    $loader = Mockery::mock(ThemeSettingsLoader::class);
    $loader->shouldReceive('loadThemeSettings')->once()->andReturn($expected);
    app()->instance(ThemeSettingsLoader::class, $loader);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    expect($theme->settings)->toBe($expected);
});

it('memoizes settings across repeated access', function () {
    $loader = Mockery::mock(ThemeSettingsLoader::class);
    $loader->shouldReceive('loadThemeSettings')->once()->andReturn(new PropertyBag);
    app()->instance(ThemeSettingsLoader::class, $loader);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    $first = $theme->settings;
    $second = $theme->settings;

    expect($first)->toBe($second);
});

it('honors explicit settings assignment without triggering the loader', function () {
    $loader = Mockery::mock(ThemeSettingsLoader::class);
    $loader->shouldNotReceive('loadThemeSettings');
    app()->instance(ThemeSettingsLoader::class, $loader);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    $bag = new PropertyBag(['foo' => 'bar']);
    $theme->settings = $bag;

    expect($theme->settings)->toBe($bag);
});

it('reflects backing field state via isset', function () {
    $loader = Mockery::mock(ThemeSettingsLoader::class);
    $loader->shouldNotReceive('loadThemeSettings');
    app()->instance(ThemeSettingsLoader::class, $loader);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    expect(isset($theme->settings))->toBeFalse();

    $theme->settings = new PropertyBag;

    expect(isset($theme->settings))->toBeTrue();

    $theme->settings = null;

    expect(isset($theme->settings))->toBeFalse();
});

it('rejects access to unknown properties', function () {
    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    expect(fn () => $theme->nonExistent)->toThrow(Error::class, 'Undefined property');
});

it('rejects writes to unknown properties', function () {
    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    expect(fn () => $theme->nonExistent = 'value')->toThrow(Error::class, 'Cannot create dynamic property');
});

// Regression for the original Livewire bug: $theme->settings->get(...) used to throw
// "Typed property must not be accessed before initialization" when called outside a
// shop::* view (e.g. during a Livewire update request). The lazy loader makes it safe.
it('allows reading settings on a freshly constructed theme without uninitialized error', function () {
    $loader = Mockery::mock(ThemeSettingsLoader::class);
    $loader->shouldReceive('loadThemeSettings')->andReturn(new PropertyBag(['some-key' => 'some-value']));
    app()->instance(ThemeSettingsLoader::class, $loader);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    expect($theme->settings->get('some-key'))->toBe('some-value');
});
