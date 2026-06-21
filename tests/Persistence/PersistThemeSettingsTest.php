<?php

use BagistoPlus\Visual\Persistence\Data\ThemeSettingsUpdateData;
use BagistoPlus\Visual\Persistence\EditorDataStore;
use BagistoPlus\Visual\Persistence\LocalizedProperties;
use BagistoPlus\Visual\Persistence\PersistThemeSettings;
use BagistoPlus\Visual\Persistence\ThemeSettingsDiffer;
use BagistoPlus\Visual\ThemePathsResolver as ThemePathsResolverConcrete;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/visual-theme-settings-persist-'.uniqid();
    config()->set('bagisto_visual.data_path', $this->dataPath);
    config()->set('themes.shop.test-theme', [
        'code' => 'test-theme',
        'name' => 'Test Theme',
        'settings_schema' => [
            [
                'name' => 'General',
                'settings' => [
                    ['id' => 'headline', 'type' => 'text', 'default' => 'Default headline', 'localized' => true],
                    ['id' => 'handle', 'type' => 'text', 'default' => 'default-handle', 'localized' => false],
                    ['id' => 'colors', 'type' => 'text', 'default' => ['primary' => '#000000'], 'localized' => false],
                ],
            ],
        ],
    ]);

    $this->files = new Filesystem;
    $this->resolver = new class extends ThemePathsResolverConcrete
    {
        public function resolveFallbackPaths(string $themeCode, string $mode, string $channel, string $locale): array
        {
            return [
                $this->buildThemePath($themeCode, $mode, $channel, $locale),
                $this->buildThemePath($themeCode, $mode, 'default', 'en'),
            ];
        }
    };
    app()->instance(ThemePathsResolverConcrete::class, $this->resolver);
    $this->store = new EditorDataStore($this->resolver, $this->files);
    $this->persistThemeSettings = new PersistThemeSettings(
        new ThemeSettingsLoader($this->resolver, $this->files, $this->store),
        $this->store,
        new ThemeSettingsDiffer($this->store),
        new LocalizedProperties,
    );
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->dataPath);
});

it('uses nearest fallback settings parent and applies dot notation updates', function () {
    put_theme_settings_file('test-theme', 'default/en/theme.json', [
        'headline' => 'English headline',
        'handle' => 'same-handle',
        'colors' => ['primary' => '#000000'],
    ]);

    $result = $this->persistThemeSettings->handle(ThemeSettingsUpdateData::fromValidated([
        'theme' => 'test-theme',
        'channel' => 'default',
        'locale' => 'ar',
        'template' => [
            'url' => 'https://example.test',
        ],
        'updates' => [
            'headline' => 'Arabic headline',
            'colors.primary' => '#ff0000',
        ],
    ]));

    $saved = json_decode($this->files->get(theme_settings_path('test-theme', 'default/ar/theme.json')), true);

    expect($result['success'])->toBeTrue()
        ->and($saved)->toBe([
            'parent' => 'default/en/theme.json',
            'headline' => 'Arabic headline',
            'colors' => ['primary' => '#ff0000'],
        ]);
});

it('forces localized settings when saving against a different locale parent and clears cache', function () {
    put_theme_settings_file('test-theme', 'default/en/theme.json', [
        'headline' => 'English headline',
        'handle' => 'same-handle',
    ]);

    Cache::put('theme_settings.test-theme.default.ar', 'cached');

    $this->persistThemeSettings->handle(ThemeSettingsUpdateData::fromValidated([
        'theme' => 'test-theme',
        'channel' => 'default',
        'locale' => 'ar',
        'template' => [
            'url' => 'https://example.test',
        ],
        'updates' => [
            'handle' => 'arabic-handle',
        ],
    ]));

    $saved = json_decode($this->files->get(theme_settings_path('test-theme', 'default/ar/theme.json')), true);

    expect($saved)->toBe([
        'parent' => 'default/en/theme.json',
        'handle' => 'arabic-handle',
        'headline' => 'English headline',
    ])->and(Cache::has('theme_settings.test-theme.default.ar'))->toBeFalse();
});

it('keeps an existing stored settings parent before nearest fallback', function () {
    put_theme_settings_file('test-theme', 'default/en/theme.json', [
        'headline' => 'Default English headline',
        'handle' => 'default-handle',
    ]);
    put_theme_settings_file('test-theme', 'mobile/en/theme.json', [
        'headline' => 'Mobile English headline',
        'handle' => 'mobile-handle',
    ]);
    put_theme_settings_file('test-theme', 'default/ar/theme.json', [
        'parent' => 'mobile/en/theme.json',
        'headline' => 'Old Arabic headline',
    ]);

    $this->persistThemeSettings->handle(ThemeSettingsUpdateData::fromValidated([
        'theme' => 'test-theme',
        'channel' => 'default',
        'locale' => 'ar',
        'template' => [
            'url' => 'https://example.test',
        ],
        'updates' => [
            'headline' => 'Arabic headline',
        ],
    ]));

    $saved = json_decode($this->files->get(theme_settings_path('test-theme', 'default/ar/theme.json')), true);

    expect($saved)->toBe([
        'parent' => 'mobile/en/theme.json',
        'headline' => 'Arabic headline',
    ]);
});

function theme_settings_path(string $theme, string $relativePath): string
{
    return rtrim(config('bagisto_visual.data_path'), '/\\')."/themes/{$theme}/editor/{$relativePath}";
}

function put_theme_settings_file(string $theme, string $relativePath, array $data): string
{
    $path = theme_settings_path($theme, $relativePath);
    (new Filesystem)->ensureDirectoryExists(dirname($path));
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return $path;
}
