<?php

use BagistoPlus\Visual\Data\BlockData;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Providers\ThemeServiceProvider;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme\Blocks\LivewireHero;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme\FakeThemeServiceProvider;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme2\Providers\FakeTheme2ServiceProvider;
use BagistoPlus\Visual\View\Compilers\LivewireBlockCompiler;
use Livewire\ComponentHookRegistry;
use Livewire\Features\SupportReleaseTokens\ReleaseToken;
use Livewire\Livewire;

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

it('should discover sections blocks and presets from the theme source directories', function () {
    Visual::spy();

    $serviceProvider = new FakeThemeServiceProvider($this->app);
    $serviceProvider->boot();

    $basePath = dirname(__DIR__).'/Fixtures/FakeTheme';
    $namespace = 'BagistoPlus\\Visual\\Tests\\Fixtures\\FakeTheme';

    $normalizesTo = fn (string $expected) => Mockery::on(
        fn (string $path) => str_replace('\\', '/', $path) === str_replace('\\', '/', $expected)
    );

    Visual::shouldHaveReceived('discoverSectionsIn')
        ->with($normalizesTo("{$basePath}/src/Sections"), "{$namespace}\\Sections")
        ->once();

    Visual::shouldHaveReceived('discoverBlocksIn')
        ->with($normalizesTo("{$basePath}/src/Blocks"), "{$namespace}\\Blocks")
        ->once();

    Visual::shouldHaveReceived('discoverPresetsIn')
        ->with($normalizesTo("{$basePath}/src/Presets"), "{$namespace}\\Presets")
        ->once();
});

it('compiles Livewire blocks using their component class name', function () {
    $compiled = (new LivewireBlockCompiler)->compile(
        BlockSchema::fromClass(LivewireHero::class),
        'abc123'
    );

    expect($compiled)
        ->toContain('@livewire(\\'.LivewireHero::class.'::class')
        ->not->toContain("@livewire('craftile-livewire-hero'");
});

it('dehydrates Visual Livewire block snapshots with a class name', function () {
    config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));

    Visual::supportLivewire();
    ComponentHookRegistry::boot();

    $finder = app('livewire.finder');
    $factory = app('livewire.factory');

    $classComponents = new ReflectionProperty($finder, 'classComponents');
    $classComponents->setValue($finder, []);

    $resolvedComponentCache = new ReflectionProperty($factory, 'resolvedComponentCache');
    $resolvedComponentCache->setValue($factory, []);

    $snapshot = Livewire::test(LivewireHero::class, [
        'context' => [],
        'block' => BlockData::make([
            'id' => 'hero-1',
            'type' => LivewireHero::type(),
        ])->setSourceFile(__FILE__),
    ])->snapshot;

    expect($snapshot['memo']['name'])->toBe(LivewireHero::class);

    ReleaseToken::verify($snapshot);

    expect($factory->resolveComponentClass($snapshot['memo']['name']))->toBe(LivewireHero::class);
});

it('preserves Visual Livewire block HTML attributes on snapshots', function () {
    config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));

    Visual::supportLivewire();
    ComponentHookRegistry::boot();

    $snapshot = Livewire::test(LivewireHero::class, [
        'context' => [],
        'block' => BlockData::make([
            'id' => 'hero-1',
            'type' => LivewireHero::type(),
        ])->setSourceFile(__FILE__),
        'x-data' => '{ open: false }',
        'data-test' => 'hero',
    ])->snapshot;

    expect($snapshot['memo']['attributes'])->toMatchArray([
        'x-data' => '{ open: false }',
        'data-test' => 'hero',
    ]);
});

test('theme should be loaded', function () {
    $theme = app('themes')->find('fake-theme');
    expect($theme)->toHaveProperties([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
    ]);
})->skip('Requires full Bagisto bootstrap with request context');
