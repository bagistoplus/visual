<?php

use BagistoPlus\Visual\Persistence\EditorDataStore;
use BagistoPlus\Visual\Settings\Select;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\ThemeEditor;
use BagistoPlus\Visual\ThemePathsResolver;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Illuminate\Filesystem\Filesystem;

function theme_settings_loader_theme(): Theme
{
    return Theme::make([
        'code' => 'test-theme',
        'name' => 'Test Theme',
        'settings_schema' => [
            [
                'name' => 'Content',
                'settings' => [
                    Text::make('title', 'Title')->default('t:settings.default_title')->toArray(),
                    Select::make('handle', 'Handle')->default('t:settings.default_handle')->toArray(),
                    Text::make('responsive_title', 'Responsive title')->responsive()->default([
                        'desktop' => 't:settings.desktop',
                        'mobile' => 't:settings.mobile',
                    ])->toArray(),
                    Text::make('structured_title', 'Structured title')->default([
                        'label' => 't:settings.structured_label',
                    ])->toArray(),
                    Select::make('raw_responsive', 'Raw responsive')->default([
                        'desktop' => 't:settings.raw_desktop',
                    ])->toArray(),
                    Text::make('missing_title', 'Missing title')->default('t:settings.missing_title')->toArray(),
                ],
            ],
        ],
    ]);
}

beforeEach(function () {
    app('translator')->addLines([
        'settings.default_title' => 'Default title',
        'settings.title' => 'Translated title',
        'settings.default_handle' => 'Default handle',
        'settings.handle' => 'Translated handle',
        'settings.desktop' => 'Desktop title',
        'settings.mobile' => 'Mobile title',
        'settings.raw_desktop' => 'Raw desktop',
        'settings.structured_label' => 'Structured label',
        'settings.missing_title' => 'Missing title',
        'settings.parent_title' => 'Parent title',
    ], 'en');
});

it('loads resolved editor parent settings before applying schema defaults', function () {
    $dataPath = sys_get_temp_dir().'/visual-theme-settings-loader-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    $files = new Filesystem;
    $files->ensureDirectoryExists("{$dataPath}/themes/test-theme/editor/default/en");
    $files->ensureDirectoryExists("{$dataPath}/themes/test-theme/editor/default/ar");
    $files->put("{$dataPath}/themes/test-theme/editor/default/en/theme.json", json_encode([
        'title' => 't:settings.parent_title',
        'handle' => 'parent-handle',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $files->put("{$dataPath}/themes/test-theme/editor/default/ar/theme.json", json_encode([
        'parent' => 'default/en/theme.json',
        'title' => 'Arabic title',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $themeEditor = Mockery::mock(ThemeEditor::class);
    $themeEditor->shouldReceive('active')->andReturnTrue();
    app()->instance(ThemeEditor::class, $themeEditor);

    $resolver = Mockery::mock(ThemePathsResolver::class);
    $resolver->shouldReceive('resolveThemeSettingsPath')
        ->once()
        ->with('test-theme', 'default', 'en', 'editor')
        ->andReturn("{$dataPath}/themes/test-theme/editor/default/ar/theme.json");
    $resolver->shouldReceive('getThemeBaseDataPath')
        ->andReturnUsing(fn (string $theme, string $mode = 'live') => "{$dataPath}/themes/{$theme}/{$mode}");
    $resolver->shouldReceive('resolveFallbackPaths')
        ->andReturn([]);

    $loader = new ThemeSettingsLoader(
        $resolver,
        $files,
        new EditorDataStore($resolver, $files),
    );

    $settings = $loader->loadThemeSettings(theme_settings_loader_theme())->toArray();

    expect($settings)->toMatchArray([
        'title' => 'Arabic title',
        'handle' => 'parent-handle',
        'missing_title' => 'Missing title',
    ]);

    $files->deleteDirectory($dataPath);
});

it('resolves translation references only for localized theme settings', function () {
    $path = sys_get_temp_dir().'/visual-theme-settings-'.uniqid().'.json';
    file_put_contents($path, json_encode([
        'title' => 't:settings.title',
        'handle' => 't:settings.handle',
        'responsive_title' => [
            'desktop' => 't:settings.desktop',
            'mobile' => 't:settings.mobile',
            'empty' => 't:',
        ],
        'structured_title' => [
            'label' => 't:settings.structured_label',
        ],
        'raw_responsive' => [
            'desktop' => 't:settings.raw_desktop',
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $themeEditor = Mockery::mock(ThemeEditor::class);
    $themeEditor->shouldReceive('active')->andReturnFalse();
    app()->instance(ThemeEditor::class, $themeEditor);

    $resolver = Mockery::mock(ThemePathsResolver::class);
    $resolver->shouldReceive('resolveThemeSettingsPath')->once()->andReturn($path);

    $files = new Filesystem;
    $loader = new ThemeSettingsLoader($resolver, $files, new EditorDataStore($resolver, $files));
    $settings = $loader->loadThemeSettings(theme_settings_loader_theme())->toArray();

    expect($settings)->toMatchArray([
        'title' => 'Translated title',
        'handle' => 't:settings.handle',
        'responsive_title' => [
            'desktop' => 'Desktop title',
            'mobile' => 'Mobile title',
            'empty' => 't:',
        ],
        'structured_title' => [
            'label' => 't:settings.structured_label',
        ],
        'raw_responsive' => [
            'desktop' => 't:settings.raw_desktop',
        ],
        'missing_title' => 'Missing title',
    ]);

    unlink($path);
});

it('uses schema defaults when theme settings json is invalid or non array', function (string $content) {
    $path = sys_get_temp_dir().'/visual-theme-settings-invalid-'.uniqid().'.json';
    file_put_contents($path, $content);

    $themeEditor = Mockery::mock(ThemeEditor::class);
    $themeEditor->shouldReceive('active')->andReturnFalse();
    app()->instance(ThemeEditor::class, $themeEditor);

    $resolver = Mockery::mock(ThemePathsResolver::class);
    $resolver->shouldReceive('resolveThemeSettingsPath')->once()->andReturn($path);

    $files = new Filesystem;
    $loader = new ThemeSettingsLoader($resolver, $files, new EditorDataStore($resolver, $files));
    $settings = $loader->loadThemeSettings(theme_settings_loader_theme())->toArray();

    expect($settings)->toMatchArray([
        'title' => 'Default title',
        'handle' => 't:settings.default_handle',
        'responsive_title' => [
            'desktop' => 'Desktop title',
            'mobile' => 'Mobile title',
        ],
        'structured_title' => [
            'label' => 't:settings.structured_label',
        ],
        'raw_responsive' => [
            'desktop' => 't:settings.raw_desktop',
        ],
    ]);

    unlink($path);
})->with([
    'invalid json' => ['{'],
    'non array json' => ['"not-array"'],
]);
