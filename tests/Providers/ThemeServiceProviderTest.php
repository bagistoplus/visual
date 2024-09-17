<?php

use BagistoPlus\Visual\Providers\ThemeServiceProvider;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme\FakeThemeServiceProvider;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme2\Providers\FakeTheme2ServiceProvider;

it('should be abstract', function () {
    expect(ThemeServiceProvider::class)->toBeAbstract();
});
it('should retrieve package base path', function () {
    $serviceProvider = new FakeThemeServiceProvider($this->app);
    $packageDir = dirname(__DIR__).'/Fixtures/FakeTheme';
    expect(realpath($serviceProvider->getBasePath()))->toBe(realpath($packageDir));

    $serviceProvider = new FakeTheme2ServiceProvider($this->app);
    $packageDir = dirname(__DIR__).'/Fixtures/FakeTheme2';
    expect(realpath($serviceProvider->getBasePath()))->toBe(realpath($packageDir));
});

it('should retrieve theme config file path', function () {
    $serviceProvider = new FakeThemeServiceProvider($this->app);
    $configPath = dirname(__DIR__).'/Fixtures/FakeTheme/config/theme.php';
    expect(realpath($serviceProvider->getThemeConfigPath()))->toBe(realpath($configPath));
});

it('should load theme config into `themes.shop`', function () {
    expect(config('themes.shop'))->toHaveKey('fake-theme');
    expect(config('themes.shop.fake-theme'))->toMatchArray([
        'name' => 'Fake Theme',
        'code' => 'fake-theme',
    ]);
});

it('should mark theme as visual theme', function () {
    $config = config('themes.shop.fake-theme');
    expect($config)->toHaveKey('visual_theme');
    expect($config['visual_theme'])->toBeTrue();
});

test('theme should be loaded', function () {
    $theme = app('themes')->find('fake-theme');
    expect($theme)->toHaveProperties([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
    ]);
});
