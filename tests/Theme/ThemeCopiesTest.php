<?php

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\Theme\ThemeCopies;
use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/visual-theme-copies-'.uniqid();
    config()->set('bagisto_visual.storages.file.path', $this->dataPath);
    config()->set('bagisto-vite.viters.fake-theme', ['hot_file' => 'fake.hot']);

    $this->store = app(ThemeDataStore::class);
    $this->copies = app(ThemeCopies::class);
    $this->copies->forget();
});

afterEach(function () {
    Visual::resolveStorageScopeUsing(null);
    (new Filesystem)->deleteDirectory($this->dataPath);
});

it('registers a copy with its parent config, its own name and description', function () {
    $this->store->putMeta('fake-theme-abc123', ['parent' => 'fake-theme', 'name' => 'Staging', 'description' => 'Winter sale']);

    $this->copies->register();

    expect(config('themes.shop.fake-theme-abc123'))->toMatchArray([
        'code' => 'fake-theme-abc123',
        'name' => 'Staging',
        'description' => 'Winter sale',
        'parent' => 'fake-theme',
        'visual_theme' => true,
        'base_path' => config('themes.shop.fake-theme.base_path'),
    ])->and(config('bagisto-vite.viters.fake-theme-abc123'))->toBe(['hot_file' => 'fake.hot']);
});

it('resolves a copy with its parent theme', function () {
    $this->store->putMeta('fake-theme-abc123', ['parent' => 'fake-theme', 'name' => 'Staging']);
    $this->copies->register();

    $theme = Theme::forCode('fake-theme-abc123');

    expect($theme->getParent()?->code)->toBe('fake-theme')
        ->and($theme->basePath)->toBe(config('themes.shop.fake-theme.base_path'));
});

it('leaves out theme data that is not a copy', function () {
    $this->store->put('fake-theme', 'default/en/theme.json', '{}');

    expect($this->copies->all())->toBe([]);
});

it('does not register a copy whose parent is not installed, and lists it as unavailable', function () {
    $this->store->putMeta('gone-abc123', ['parent' => 'gone', 'name' => 'Orphan']);

    $this->copies->register();

    expect(config('themes.shop.gone-abc123'))->toBeNull()
        ->and(array_keys($this->copies->unavailable()))->toBe(['gone-abc123']);
});

it('keeps the copies of each storage scope to that scope', function () {
    Visual::withStorageScope('tenant-a', fn () => $this->store->putMeta('fake-theme-aaa111', ['parent' => 'fake-theme', 'name' => 'A']));

    expect($this->copies->all())->toBe([]);

    Visual::resolveStorageScopeUsing(fn () => 'tenant-a');

    expect(array_keys($this->copies->all()))->toBe(['fake-theme-aaa111']);
});

it('remembers the copies until told to forget them', function () {
    expect($this->copies->all())->toBe([]);

    $this->store->putMeta('fake-theme-abc123', ['parent' => 'fake-theme', 'name' => 'Staging']);

    expect($this->copies->all())->toBe([]);

    $this->copies->forget();

    expect(array_keys($this->copies->all()))->toBe(['fake-theme-abc123']);
});

it('keeps the copies each storage driver holds apart', function () {
    migrateThemeDataTables();
    $this->store->putMeta('fake-theme-abc123', ['parent' => 'fake-theme', 'name' => 'Staging']);

    expect(array_keys($this->copies->all()))->toBe(['fake-theme-abc123']);

    config()->set('bagisto_visual.storage', 'database');

    expect($this->copies->all())->toBe([]);
});
