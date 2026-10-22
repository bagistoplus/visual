<?php

use BagistoPlus\Visual\Persistence\PublishTheme;
use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/visual-publish-theme-'.uniqid();
    config()->set('bagisto_visual.data_path', $this->dataPath);

    $this->files = new Filesystem;
    $this->themePath = $this->dataPath.'/themes/demo';

    $this->files->ensureDirectoryExists($this->themePath.'/editor/default/en/templates');
    $this->files->put($this->themePath.'/editor/default/en/templates/index.json', json_encode(['blocks' => [], 'regions' => []]));
    $this->files->put($this->themePath.'/editor/.last-edit', (string) time());

    $this->versionDirs = fn () => collect($this->files->directories($this->themePath.'/versions'))
        ->map(fn (string $path) => basename($path))
        ->sort()
        ->values()
        ->all();
});

afterEach(function () {
    $this->files->deleteDirectory($this->dataPath);
});

it('snapshots the editor data into a version and mirrors it to live', function () {
    app(PublishTheme::class)->handle('demo');

    $versions = ($this->versionDirs)();

    expect($versions)->toHaveCount(1)
        ->and($versions[0])->toMatch('/^V\d+$/')
        ->and($this->files->exists($this->themePath.'/versions/'.$versions[0].'/default/en/templates/index.json'))->toBeTrue()
        ->and($this->files->exists($this->themePath.'/live/default/en/templates/index.json'))->toBeTrue()
        ->and($this->files->exists($this->themePath.'/editor/.last-edit'))->toBeFalse();
});

it('keeps only the newest configured number of versions', function () {
    config()->set('bagisto_visual.versions_to_keep', 2);

    foreach (['V1000', 'V2000', 'V3000'] as $version) {
        $this->files->ensureDirectoryExists($this->themePath.'/versions/'.$version);
    }

    app(PublishTheme::class)->handle('demo');

    $versions = ($this->versionDirs)();
    $published = collect($versions)->first(fn (string $version) => ! in_array($version, ['V1000', 'V2000', 'V3000'], true));

    expect($published)->toMatch('/^V\d{10,}$/')
        ->and($versions)->toEqualCanonicalizing(['V3000', $published]);
});

it('keeps no versions when configured to zero but still publishes to live', function () {
    config()->set('bagisto_visual.versions_to_keep', 0);

    $this->files->ensureDirectoryExists($this->themePath.'/versions/V1000');

    app(PublishTheme::class)->handle('demo');

    expect(($this->versionDirs)())->toBe([])
        ->and($this->files->exists($this->themePath.'/live/default/en/templates/index.json'))->toBeTrue();
});

it('publishes an editor holding nothing but its last edit marker', function () {
    $this->files->delete($this->themePath.'/editor/default/en/templates/index.json');

    app(PublishTheme::class)->handle('demo');

    $versions = ($this->versionDirs)();

    expect($versions)->toHaveCount(1)
        ->and($versions[0])->toMatch('/^V\d+$/')
        ->and($this->files->isDirectory($this->themePath.'/live'))->toBeTrue()
        ->and($this->files->allFiles($this->themePath.'/live'))->toBe([])
        ->and($this->files->exists($this->themePath.'/editor/.last-edit'))->toBeFalse();
});

it('leaves directories that are not versions untouched', function () {
    config()->set('bagisto_visual.versions_to_keep', 0);

    $this->files->ensureDirectoryExists($this->themePath.'/versions/manual-backup');

    app(PublishTheme::class)->handle('demo');

    expect(($this->versionDirs)())->toBe(['manual-backup']);
});
