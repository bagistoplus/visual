<?php

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Persistence\EditorDataStore;
use BagistoPlus\Visual\Persistence\ThemeSettingsCache;
use BagistoPlus\Visual\Support\TemplateNormalizer;
use BagistoPlus\Visual\ThemePathsResolver;
use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/visual-storage-scope-'.uniqid();
    config()->set('bagisto_visual.storages.file.path', $this->dataPath);
    config()->set('bagisto_visual.images.directory', 'bagisto-visual/images');
    config()->set('bagisto_visual.videos.directory', 'bagisto-visual/videos');
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->dataPath);
});

it('keeps everything where it was when no scope is set', function () {
    expect(Visual::storageScope())->toBe('')
        ->and((new ThemePathsResolver)->getThemeBaseDataPath('demo', 'editor'))->toBe("{$this->dataPath}/themes/demo/editor")
        ->and(ThemeSettingsCache::key('demo', 'default', 'en'))->toBe('theme_settings.demo.default.en')
        ->and(Visual::imagesDirectory())->toBe('bagisto-visual/images')
        ->and(Visual::videosDirectory())->toBe('bagisto-visual/videos');
});

it('treats a resolver returning nothing as no scope', function (mixed $value) {
    Visual::resolveStorageScopeUsing(fn () => $value);

    expect(Visual::storageScope())->toBe('');
})->with([null, '']);

it('keeps theme data, settings cache and media under the scope', function () {
    Visual::resolveStorageScopeUsing(fn () => 'acme');

    expect((new ThemePathsResolver)->getThemeBaseDataPath('demo', 'editor'))->toBe("{$this->dataPath}/acme/themes/demo/editor")
        ->and(ThemeSettingsCache::key('demo', 'default', 'en'))->toBe('acme:theme_settings.demo.default.en')
        ->and(Visual::imagesDirectory())->toBe('acme/bagisto-visual/images')
        ->and(Visual::videosDirectory())->toBe('acme/bagisto-visual/videos');
});

it('accepts numeric and dotted scopes', function () {
    Visual::resolveStorageScopeUsing(fn () => 42);
    expect(Visual::storageScope())->toBe('42');

    Visual::resolveStorageScopeUsing(fn () => 'shop.example.com');
    expect(Visual::storageScope())->toBe('shop.example.com');
});

it('refuses a scope that is not a single safe path segment', function (string $scope) {
    Visual::resolveStorageScopeUsing(fn () => $scope);

    expect(fn () => Visual::storageScope())->toThrow(InvalidArgumentException::class);
})->with(['..', '.hidden', 'a/b', 'a\\b', '../etc', 'a b', 'tenant:1']);

it('asks the resolver every time, so it follows the tenant the host switches to', function () {
    $tenant = 'acme';
    Visual::resolveStorageScopeUsing(function () use (&$tenant) {
        return $tenant;
    });

    $first = Visual::storageScope();
    $tenant = 'globex';

    expect($first)->toBe('acme')
        ->and(Visual::storageScope())->toBe('globex');
});

it('never shows one scope the editor data of another', function () {
    $tenant = 'acme';
    Visual::resolveStorageScopeUsing(function () use (&$tenant) {
        return $tenant;
    });

    $editorData = app(EditorDataStore::class);
    $editorData->save('demo', 'default/en/theme.json', ['brand' => '#000'], null);

    expect($editorData->loadResolved('demo', 'default/en/theme.json'))->toBe(['brand' => '#000'])
        ->and(app(ThemeDataStore::class)->hasUnpublishedEdits('demo'))->toBeTrue();

    $tenant = 'globex';

    expect($editorData->loadResolved('demo', 'default/en/theme.json'))->toBe([])
        ->and(app(ThemeDataStore::class)->hasUnpublishedEdits('demo'))->toBeFalse()
        ->and(file_exists("{$this->dataPath}/acme/themes/demo/editor/default/en/theme.json"))->toBeTrue();
});

it('reads relative editor paths inside the scope', function () {
    Visual::resolveStorageScopeUsing(fn () => 'acme');

    $editorData = app(EditorDataStore::class);

    expect($editorData->relativePathFromAbsolute('demo', "{$this->dataPath}/acme/themes/demo/editor/default/en/theme.json"))
        ->toBe('default/en/theme.json')
        ->and($editorData->relativePathFromAbsolute('demo', "{$this->dataPath}/themes/demo/editor/default/en/theme.json"))
        ->toBeNull();
});

it('finds the theme of an editor template inside the scope', function () {
    Visual::resolveStorageScopeUsing(fn () => 'acme');

    $normalizer = new class extends TemplateNormalizer
    {
        public function themeOf(string $path): ?string
        {
            return $this->themeFromEditorPath($path);
        }
    };

    expect($normalizer->themeOf("{$this->dataPath}/acme/themes/demo/editor/default/en/templates/index.json"))->toBe('demo')
        ->and($normalizer->themeOf("{$this->dataPath}/themes/demo/editor/default/en/templates/index.json"))->toBeNull();
});
