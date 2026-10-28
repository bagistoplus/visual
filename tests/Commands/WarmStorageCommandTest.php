<?php

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Persistence\DatabaseThemeDataStore;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config()->set('bagisto_visual.storage', 'database');
    migrateThemeDataTables();

    $this->store = app(DatabaseThemeDataStore::class);
});

afterEach(function () {
    Visual::resolveStorageScopeUsing(null);
});

function publishIn(DatabaseThemeDataStore $store, string $scope, string $theme, string $contents): void
{
    Visual::withStorageScope($scope, fn () => $store->publish($theme, ['default/en/theme.json' => $contents]));
}

it('fills the cache so the storefront reads published data without the database', function () {
    publishIn($this->store, '', 'demo', '{"a":1}');
    Cache::flush();

    $this->artisan('visual:storage:warm')->assertExitCode(0);

    $fresh = new DatabaseThemeDataStore;

    expect(countThemeDataQueries(fn () => expect($fresh->contents('', 'demo', 'live', 'default/en/theme.json'))->toBe('{"a":1}')))->toBe(0);
});

it('warms every scope unless told otherwise', function () {
    publishIn($this->store, '', 'demo', '{"none":1}');
    publishIn($this->store, 'acme', 'demo', '{"acme":1}');
    publishIn($this->store, 'acme', 'other', '{"other":1}');
    Cache::flush();

    $this->artisan('visual:storage:warm')->assertExitCode(0);

    expect(Cache::get('visual:theme-data:_:demo:live'))->not->toBeNull()
        ->and(Cache::get('visual:theme-data:acme:demo:live'))->not->toBeNull()
        ->and(Cache::get('visual:theme-data:acme:other:live'))->not->toBeNull();
});

it('warms only the scope and theme asked for', function () {
    publishIn($this->store, '', 'demo', '{"none":1}');
    publishIn($this->store, 'acme', 'demo', '{"acme":1}');
    publishIn($this->store, 'acme', 'other', '{"other":1}');
    Cache::flush();

    $this->artisan('visual:storage:warm', ['--scope' => 'acme', '--theme' => 'other'])->assertExitCode(0);

    expect(Cache::get('visual:theme-data:acme:other:live'))->not->toBeNull()
        ->and(Cache::get('visual:theme-data:acme:demo:live'))->toBeNull()
        ->and(Cache::get('visual:theme-data:_:demo:live'))->toBeNull();
});

it('skips themes that were never published', function () {
    $this->store->put('demo', 'default/en/theme.json', '{}');
    Cache::flush();

    $this->artisan('visual:storage:warm')
        ->expectsOutputToContain('No published theme data to warm.')
        ->assertExitCode(0);

    expect(Cache::get('visual:theme-data:_:demo:live'))->toBeNull();
});

it('leaves a newer live pointer in place', function () {
    publishIn($this->store, '', 'demo', '{"a":1}');
    Cache::flush();
    Cache::put('visual:theme-data:_:demo:live', 999, 300);

    $this->artisan('visual:storage:warm')->assertExitCode(0);

    expect(Cache::get('visual:theme-data:_:demo:live'))->toBe(999);
});

it('does nothing in file mode', function () {
    config()->set('bagisto_visual.storage', 'file');
    publishIn($this->store, '', 'demo', '{"a":1}');
    Cache::flush();

    $this->artisan('visual:storage:warm')
        ->expectsOutputToContain('nothing to warm')
        ->assertExitCode(0);

    expect(Cache::get('visual:theme-data:_:demo:live'))->toBeNull();
});

it('rejects a scope that cannot be one', function () {
    $this->artisan('visual:storage:warm', ['--scope' => '../etc'])->assertExitCode(1);
});
