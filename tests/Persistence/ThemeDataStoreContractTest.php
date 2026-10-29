<?php

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Data\ThemeSnapshot;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Persistence\DatabaseThemeDataStore;
use BagistoPlus\Visual\Persistence\FileThemeDataStore;
use BagistoPlus\Visual\Persistence\VisualStreamWrapper;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/visual-store-contract-'.uniqid();
    config()->set('bagisto_visual.storages.file.path', $this->dataPath);
    migrateThemeDataTables();
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->dataPath);
});

dataset('stores', [
    'file' => fn () => app(FileThemeDataStore::class),
    'database' => fn () => app(DatabaseThemeDataStore::class),
]);

function storeVersionCount(ThemeDataStore $store, string $theme): int
{
    if ($store instanceof DatabaseThemeDataStore) {
        return DB::table('visual_theme_versions')->where('theme', $theme)->count();
    }

    $path = test()->dataPath."/themes/{$theme}/versions";

    return is_dir($path) ? count((new Filesystem)->directories($path)) : 0;
}

it('holds an editor document and flags the theme as edited', function (ThemeDataStore $store) {
    $store->put('demo', 'default/en/theme.json', '{"a":1}');

    $snapshot = $store->snapshot('demo');

    expect($snapshot->editor)->toBe(['default/en/theme.json' => '{"a":1}'])
        ->and($snapshot->live)->toBeNull()
        ->and($snapshot->edited)->toBeTrue()
        ->and($store->hasUnpublishedEdits('demo'))->toBeTrue();
})->with('stores');

it('removes an editor document and flags the theme as edited', function (ThemeDataStore $store) {
    $store->put('demo', 'default/en/theme.json', '{}');
    $store->publish('demo', ['default/en/theme.json' => '{}']);

    $store->delete('demo', 'default/en/theme.json');

    expect($store->snapshot('demo')->editor)->toBe([])
        ->and($store->hasUnpublishedEdits('demo'))->toBeTrue();
})->with('stores');

it('publishes documents without touching the editor ones', function (ThemeDataStore $store) {
    $store->put('demo', 'default/en/theme.json', '{"draft":1}');

    $store->publish('demo', ['default/en/theme.json' => '{"live":1}']);

    expect($store->snapshot('demo'))->toEqual(new ThemeSnapshot(
        editor: ['default/en/theme.json' => '{"draft":1}'],
        live: ['default/en/theme.json' => '{"live":1}'],
        edited: false,
    ));
})->with('stores');

it('discards the editor documents in favour of the published ones', function (ThemeDataStore $store) {
    $store->publish('demo', ['default/en/theme.json' => '{"live":1}']);
    $store->put('demo', 'default/fr/theme.json', '{"draft":1}');

    $store->discard('demo');

    expect($store->snapshot('demo')->editor)->toBe(['default/en/theme.json' => '{"live":1}'])
        ->and($store->hasUnpublishedEdits('demo'))->toBeFalse();
})->with('stores');

it('lists the scopes it holds data for, no scope first', function (ThemeDataStore $store) {
    expect($store->scopes())->toBe([]);

    Visual::withStorageScope('globex', fn () => $store->put('demo', 'default/en/theme.json', '{}'));
    $store->put('demo', 'default/en/theme.json', '{}');
    Visual::withStorageScope('acme', fn () => $store->put('demo', 'default/en/theme.json', '{}'));

    expect($store->scopes())->toBe(['', 'acme', 'globex']);
})->with('stores');

it('lists the themes of the current scope only', function (ThemeDataStore $store) {
    $store->put('demo', 'default/en/theme.json', '{}');
    $store->put('another', 'default/en/theme.json', '{}');
    Visual::withStorageScope('acme', fn () => $store->put('tenant-theme', 'default/en/theme.json', '{}'));

    expect($store->themes())->toBe(['another', 'demo'])
        ->and(Visual::withStorageScope('acme', fn () => $store->themes()))->toBe(['tenant-theme']);
})->with('stores');

it('restores a snapshot over what it held, keeping its versions', function (ThemeDataStore $store) {
    $store->put('demo', 'default/en/theme.json', '{"old":1}');
    $store->put('demo', 'default/en/templates/index.json', '{"old":1}');
    $store->publish('demo', ['default/en/theme.json' => '{"old":1}']);
    $this->travel(2)->seconds();

    $snapshot = new ThemeSnapshot(
        editor: ['default/en/theme.json' => '{"new":1}', 'default/fr/theme.json' => '{"new":2}'],
        live: ['default/en/theme.json' => '{"new":1}'],
        edited: true,
    );
    $store->restore('demo', $snapshot);

    expect($store->snapshot('demo'))->toEqual($snapshot)
        ->and($store->hasUnpublishedEdits('demo'))->toBeTrue()
        ->and(storeVersionCount($store, 'demo'))->toBe(2);
})->with('stores');

it('leaves what is live alone when the snapshot was never published', function (ThemeDataStore $store) {
    $store->publish('demo', ['default/en/theme.json' => '{"live":1}']);

    $store->restore('demo', new ThemeSnapshot(editor: ['default/en/theme.json' => '{"draft":1}'], live: null, edited: false));

    expect($store->snapshot('demo'))->toEqual(new ThemeSnapshot(
        editor: ['default/en/theme.json' => '{"draft":1}'],
        live: ['default/en/theme.json' => '{"live":1}'],
        edited: false,
    ));
})->with('stores');

it('holds what a theme data says about the theme', function (ThemeDataStore $store) {
    expect($store->meta('demo'))->toBeNull();

    $store->putMeta('demo', ['parent' => 'fake-theme', 'name' => 'Demo copy']);

    expect($store->meta('demo'))->toBe(['parent' => 'fake-theme', 'name' => 'Demo copy'])
        ->and($store->themes())->toBe(['demo']);

    $store->putMeta('demo', null);

    expect($store->meta('demo'))->toBeNull();
})->with('stores');

it('carries the meta in a snapshot and restores it', function (ThemeDataStore $store) {
    $store->put('demo', 'default/en/theme.json', '{"a":1}');
    $store->putMeta('demo', ['parent' => 'fake-theme', 'name' => 'Demo copy']);

    $store->restore('other', $store->snapshot('demo'));

    expect($store->meta('other'))->toBe(['parent' => 'fake-theme', 'name' => 'Demo copy'])
        ->and($store->snapshot('other')->editor)->toBe(['default/en/theme.json' => '{"a":1}']);
})->with('stores');

it('deletes everything held for one theme and nothing of another', function (ThemeDataStore $store) {
    foreach (['demo', 'other'] as $theme) {
        $store->publish($theme, ['default/en/theme.json' => '{"live":1}']);
        $store->put($theme, 'default/en/theme.json', '{"draft":1}');
        $store->putMeta($theme, ['parent' => 'fake-theme', 'name' => $theme]);
    }

    $store->deleteTheme('demo');

    expect($store->themes())->toBe(['other'])
        ->and($store->snapshot('demo'))->toEqual(new ThemeSnapshot(editor: [], live: null, edited: false))
        ->and(storeVersionCount($store, 'demo'))->toBe(0)
        ->and($store->snapshot('other')->live)->toBe(['default/en/theme.json' => '{"live":1}']);
})->with('stores');

it('serves nothing published by a deleted theme to a theme made again under its code', function () {
    $store = app(DatabaseThemeDataStore::class);
    VisualStreamWrapper::register($store);
    $store->publish('demo', ['default/en/theme.json' => '{"old":1}']);
    expect(file_get_contents($store->themesPath('').'/demo/live/default/en/theme.json'))->toBe('{"old":1}');

    $store->deleteTheme('demo');
    $store->flush();

    expect(@file_get_contents($store->themesPath('').'/demo/live/default/en/theme.json'))->toBeFalse();
});
