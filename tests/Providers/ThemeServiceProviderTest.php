<?php

use BagistoPlus\Visual\Data\BlockData;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Events\ServingThemeEditor;
use BagistoPlus\Visual\Events\ThemeActivated;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Providers\ThemeServiceProvider;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme\Blocks\LivewireHero;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme\FakeThemeServiceProvider;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme2\Providers\FakeTheme2ServiceProvider;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\View\Compilers\LivewireBlockCompiler;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
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
    $theme = themes()->find('fake-theme');
    expect($theme)->toHaveProperties([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
    ]);
})->skip('Requires full Bagisto bootstrap with request context');

describe('when the theme is active', function () {
    function themeActivations(): ArrayObject
    {
        $activations = new ArrayObject;

        $provider = new class(app()) extends FakeThemeServiceProvider
        {
            protected $basePath = __DIR__.'/../Fixtures/FakeTheme';

            public function listen(Closure $callback): void
            {
                $this->whenActive($callback);
            }
        };

        $provider->listen(fn (Theme $theme) => $activations->append($theme->code));

        return $activations;
    }

    beforeEach(function () {
        config()->set('themes.shop.fake-theme-abc123', ['parent' => 'fake-theme'] + config('themes.shop.fake-theme'));
        config()->set('themes.shop.other', ['name' => 'Other', 'visual_theme' => true]);
    });

    it('runs for the theme and for its copies when they are activated', function () {
        $activations = themeActivations();

        foreach (['fake-theme', 'fake-theme-abc123', 'other'] as $code) {
            ThemeActivated::dispatch(Theme::forCode($code));
        }

        expect($activations->getArrayCopy())->toBe(['fake-theme', 'fake-theme-abc123']);
    });

    it('runs with the copy when the editor opens on a copy', function () {
        $activations = themeActivations();

        ServingThemeEditor::dispatch(request());

        foreach (['fake-theme-abc123', 'other'] as $code) {
            $route = (new Route('GET', 'visual/editor/{theme}', fn () => null))->bind(request()->create("/visual/editor/{$code}"));
            event(new RouteMatched($route, request()));
        }

        expect($activations->getArrayCopy())->toBe(['fake-theme-abc123']);
    });

    it('ignores a matched route that names no theme', function () {
        $activations = themeActivations();

        ServingThemeEditor::dispatch(request());

        $route = (new Route('GET', 'products', fn () => null))->bind(request()->create('/products'));
        event(new RouteMatched($route, request()));

        expect($activations->getArrayCopy())->toBe([]);
    });
});
