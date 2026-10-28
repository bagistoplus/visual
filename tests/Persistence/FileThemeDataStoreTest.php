<?php

use BagistoPlus\Visual\Persistence\FileThemeDataStore;
use BagistoPlus\Visual\ThemePathsResolver;
use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/visual-file-store-'.uniqid();
    config()->set('bagisto_visual.storages.file.path', $this->dataPath);

    $this->themePath = $this->dataPath.'/themes/demo';
    $this->store = new FileThemeDataStore(new ThemePathsResolver, new Filesystem);
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->dataPath);
});

it('writes an editor document and marks the theme as edited', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');

    expect(file_get_contents($this->themePath.'/editor/default/en/theme.json'))->toBe('{"a":1}')
        ->and($this->store->hasUnpublishedEdits('demo'))->toBeTrue();
});

it('marks the theme as edited when deleting a document it does not hold', function () {
    $this->store->delete('demo', 'default/en/theme.json');

    expect($this->store->hasUnpublishedEdits('demo'))->toBeTrue();
});

it('publishes the given documents to live and clears the edit marker', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"parent":"x","a":1}');

    $this->store->publish('demo', ['default/en/theme.json' => '{"a":1}']);

    expect(file_get_contents($this->themePath.'/live/default/en/theme.json'))->toBe('{"a":1}')
        ->and($this->store->hasUnpublishedEdits('demo'))->toBeFalse();
});

it('discards the editor documents in favour of what is published', function () {
    $this->store->publish('demo', ['default/en/theme.json' => '{"a":1}']);
    $this->store->put('demo', 'default/en/theme.json', '{"a":2}');
    $this->store->put('demo', 'default/fr/theme.json', '{"a":3}');

    $this->store->discard('demo');

    expect(file_get_contents($this->themePath.'/editor/default/en/theme.json'))->toBe('{"a":1}')
        ->and(file_exists($this->themePath.'/editor/default/fr/theme.json'))->toBeFalse()
        ->and($this->store->hasUnpublishedEdits('demo'))->toBeFalse();
});

it('keeps theme data under the file storage path', function () {
    expect($this->store->themesPath(''))->toBe($this->dataPath.'/themes')
        ->and($this->store->themesPath('acme'))->toBe($this->dataPath.'/acme/themes');
});
