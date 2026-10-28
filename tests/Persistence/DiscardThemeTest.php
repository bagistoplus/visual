<?php

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Persistence\DiscardTheme;
use BagistoPlus\Visual\Persistence\ThemeSettingsCache;
use Illuminate\Filesystem\Filesystem;

function discardTheme(): DiscardTheme
{
    return new DiscardTheme(app(ThemeDataStore::class), new ThemeSettingsCache);
}

function themeDataPath(string $mode): string
{
    return ThemePathsResolver::getThemeBaseDataPath('default', $mode);
}

function writeThemeData(string $mode, string $contents): void
{
    $path = themeDataPath($mode).'/default/en';

    (new Filesystem)->ensureDirectoryExists($path);

    file_put_contents($path.'/theme.json', $contents);
}

afterEach(function () {
    (new Filesystem)->deleteDirectory(dirname(themeDataPath('editor')));
});

it('puts back what was published over what was being edited', function () {
    writeThemeData('live', '{"brand":"#000000"}');
    writeThemeData('editor', '{"brand":"#ffffff"}');

    discardTheme()->handle('default');

    expect(file_get_contents(themeDataPath('editor').'/default/en/theme.json'))
        ->toBe('{"brand":"#000000"}');
});

it('leaves nothing behind when nothing was ever published', function () {
    writeThemeData('editor', '{"brand":"#ffffff"}');

    discardTheme()->handle('default');

    expect(file_exists(themeDataPath('editor')))->toBeFalse();
});

it('drops the marker that said there was something to publish', function () {
    writeThemeData('editor', '{"brand":"#ffffff"}');
    file_put_contents(themeDataPath('editor').'/.last-edit', (string) time());

    discardTheme()->handle('default');

    expect(file_exists(themeDataPath('editor').'/.last-edit'))->toBeFalse();
});

it('does nothing at all when the theme was never edited', function () {
    discardTheme()->handle('default');

    expect(file_exists(themeDataPath('editor')))->toBeFalse();
});
