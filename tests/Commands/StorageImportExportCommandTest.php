<?php

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Persistence\DatabaseThemeDataStore;
use BagistoPlus\Visual\Persistence\FileThemeDataStore;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/visual-storage-copy-'.uniqid();
    config()->set('bagisto_visual.storages.file.path', $this->dataPath);
    migrateThemeDataTables();

    $this->files = app(FileThemeDataStore::class);
    $this->database = app(DatabaseThemeDataStore::class);
});

afterEach(function () {
    Visual::resolveStorageScopeUsing(null);
    (new Filesystem)->deleteDirectory($this->dataPath);
});

function seedThemeFiles(FileThemeDataStore $store, string $theme): void
{
    $store->put($theme, 'default/en/theme.json', '{"title":"Live"}');
    $store->put($theme, 'default/en/templates/index.json', '{"blocks":[]}');
    $store->publish($theme, ['default/en/theme.json' => '{"title":"Live"}', 'default/en/templates/index.json' => '{"blocks":[]}']);
    $store->put($theme, 'default/en/theme.json', '{"title":"Draft"}');
    $store->put($theme, 'default/fr/theme.json', '{"title":"Brouillon"}');
}

it('copies files into the database and back without losing anything, whichever storage the app uses', function (string $storage) {
    seedThemeFiles($this->files, 'demo');
    config()->set('bagisto_visual.storage', $storage);
    $original = $this->files->snapshot('demo');

    $this->artisan('visual:storage:import')->assertExitCode(0);
    (new Filesystem)->deleteDirectory($this->dataPath);
    $this->artisan('visual:storage:export')->assertExitCode(0);

    expect($this->database->snapshot('demo'))->toEqual($original)
        ->and($this->files->snapshot('demo'))->toEqual($original)
        ->and($original->edited)->toBeTrue();
})->with(['file', 'database']);

it('refuses to replace theme data the target holds unless forced', function () {
    seedThemeFiles($this->files, 'demo');
    $this->database->put('demo', 'default/en/theme.json', '{"title":"Kept"}');

    $this->artisan('visual:storage:import')->assertExitCode(1);

    expect($this->database->snapshot('demo')->editor)->toBe(['default/en/theme.json' => '{"title":"Kept"}']);

    $this->artisan('visual:storage:import', ['--force' => true])->assertExitCode(0);

    expect($this->database->snapshot('demo'))->toEqual($this->files->snapshot('demo'));
});

it('keeps the versions the target held when forced', function () {
    seedThemeFiles($this->files, 'demo');
    $this->database->publish('demo', ['default/en/theme.json' => '{"title":"Before"}']);

    $this->artisan('visual:storage:import', ['--force' => true])->assertExitCode(0);

    expect(DB::table('visual_theme_versions')->where('theme', 'demo')->count())->toBe(2);
});

it('copies only the theme asked for', function () {
    seedThemeFiles($this->files, 'demo');
    seedThemeFiles($this->files, 'other');

    $this->artisan('visual:storage:import', ['--theme' => 'other'])->assertExitCode(0);

    expect($this->database->themes())->toBe(['other']);
});

it('fails for a theme the source holds nothing for', function () {
    seedThemeFiles($this->files, 'demo');

    $this->artisan('visual:storage:import', ['--theme' => 'missing'])->assertExitCode(1);

    expect($this->database->themes())->toBe([]);
});

it('copies every scope the source holds by default', function () {
    seedThemeFiles($this->files, 'demo');
    Visual::withStorageScope('acme', fn () => seedThemeFiles($this->files, 'tenant-theme'));

    $this->artisan('visual:storage:import')->assertExitCode(0);

    expect($this->database->scopes())->toBe(['', 'acme'])
        ->and($this->database->themes())->toBe(['demo'])
        ->and(Visual::withStorageScope('acme', fn () => $this->database->snapshot('tenant-theme')))
        ->toEqual(Visual::withStorageScope('acme', fn () => $this->files->snapshot('tenant-theme')));
});

it('copies only the scope asked for and gives the host its resolver back', function () {
    Visual::withStorageScope('acme', fn () => seedThemeFiles($this->files, 'demo'));
    seedThemeFiles($this->files, 'unscoped');
    Visual::resolveStorageScopeUsing(fn () => 'globex');

    $this->artisan('visual:storage:import', ['--scope' => 'acme'])->assertExitCode(0);

    expect(Visual::storageScope())->toBe('globex')
        ->and($this->database->scopes())->toBe(['acme'])
        ->and(Visual::withStorageScope('acme', fn () => $this->database->themes()))->toBe(['demo']);
});

it('copies the theme asked for in every scope that holds it', function () {
    seedThemeFiles($this->files, 'demo');
    seedThemeFiles($this->files, 'other');
    Visual::withStorageScope('acme', fn () => seedThemeFiles($this->files, 'demo'));
    Visual::withStorageScope('globex', fn () => seedThemeFiles($this->files, 'other'));

    $this->artisan('visual:storage:import', ['--theme' => 'demo'])->assertExitCode(0);

    expect($this->database->scopes())->toBe(['', 'acme'])
        ->and($this->database->themes())->toBe(['demo'])
        ->and(Visual::withStorageScope('acme', fn () => $this->database->themes()))->toBe(['demo']);
});

it('names the taken themes of every scope and writes nothing without --force', function () {
    seedThemeFiles($this->files, 'demo');
    Visual::withStorageScope('acme', fn () => seedThemeFiles($this->files, 'demo'));
    Visual::withStorageScope('acme', fn () => seedThemeFiles($this->files, 'free'));
    $this->database->put('demo', 'default/en/theme.json', '{"title":"Kept"}');
    Visual::withStorageScope('acme', fn () => $this->database->put('demo', 'default/en/theme.json', '{"title":"Kept"}'));

    $this->artisan('visual:storage:import')
        ->expectsOutputToContain('no scope / demo, acme / demo')
        ->assertExitCode(1);

    expect(Visual::withStorageScope('acme', fn () => $this->database->themes()))->toBe(['demo']);
});

it('rejects a scope that cannot be one', function () {
    $this->artisan('visual:storage:import', ['--scope' => '../etc'])->assertExitCode(1);
});

it('copies the editor of a theme that was never published and leaves what is live alone', function () {
    $this->files->put('demo', 'default/en/theme.json', '{"title":"Draft"}');
    $this->database->publish('demo', ['default/en/theme.json' => '{"title":"Live"}']);

    $this->artisan('visual:storage:import', ['--force' => true])->assertExitCode(0);

    expect($this->database->snapshot('demo')->editor)->toBe(['default/en/theme.json' => '{"title":"Draft"}'])
        ->and($this->database->snapshot('demo')->live)->toBe(['default/en/theme.json' => '{"title":"Live"}']);
});

it('carries what a theme copy data says about it', function () {
    seedThemeFiles($this->files, 'fake-theme-abc123');
    $this->files->putMeta('fake-theme-abc123', ['parent' => 'fake-theme', 'name' => 'Staging']);

    $this->artisan('visual:storage:import')->assertExitCode(0);

    expect($this->database->meta('fake-theme-abc123'))->toBe(['parent' => 'fake-theme', 'name' => 'Staging']);
});
