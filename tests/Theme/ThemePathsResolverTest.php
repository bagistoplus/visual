<?php

use BagistoPlus\Visual\ThemePathsResolver;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    config()->set('bagisto_visual.data_path', sys_get_temp_dir().'/visual-theme-paths-'.uniqid());
});

afterEach(function () {
    File::deleteDirectory(config('bagisto_visual.data_path'));
});

it('builds exact theme data paths without touching the database', function () {
    $resolver = new ThemePathsResolver;
    $base = config('bagisto_visual.data_path').'/themes/visual-debut/editor';

    expect($resolver->getThemeBaseDataPath('visual-debut', 'editor'))->toBe($base)
        ->and($resolver->buildThemePath('visual-debut', 'editor', 'default', 'en'))->toBe("{$base}/default/en")
        ->and($resolver->resolvePath('visual-debut', 'default', 'en', 'editor', 'templates/page.json'))
        ->toBe("{$base}/default/en/templates/page.json");
});

it('returns the first existing theme settings file from resolved fallback paths', function () {
    $base = config('bagisto_visual.data_path').'/themes/visual-debut/live';
    $resolver = new class($base) extends ThemePathsResolver
    {
        public function __construct(protected string $base) {}

        public function resolveFallbackPaths(string $themeCode, string $mode, string $channel, string $locale): array
        {
            return [
                "{$this->base}/indie/de",
                "{$this->base}/indie/fr",
                "{$this->base}/default/de",
                "{$this->base}/default/en",
            ];
        }
    };

    File::ensureDirectoryExists("{$base}/default/de");
    File::put("{$base}/default/de/theme.json", '{}');
    File::ensureDirectoryExists("{$base}/default/en");
    File::put("{$base}/default/en/theme.json", '{}');

    expect($resolver->resolveThemeSettingsPath('visual-debut', 'indie', 'de'))->toBe("{$base}/default/de/theme.json");
});

it('returns null when no theme settings fallback file exists', function () {
    $resolver = new class extends ThemePathsResolver
    {
        public function resolveFallbackPaths(string $themeCode, string $mode, string $channel, string $locale): array
        {
            return [
                config('bagisto_visual.data_path').'/themes/visual-debut/live/default/en',
            ];
        }
    };

    expect($resolver->resolveThemeSettingsPath('visual-debut', 'default', 'en'))->toBeNull();
});
