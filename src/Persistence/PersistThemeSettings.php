<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class PersistThemeSettings
{
    public function __construct(
        protected ThemeSettingsLoader $themeSettingsLoader
    ) {}

    public function handle(array $data): array
    {
        $theme = $data['theme'];
        $channel = $data['channel'];
        $locale = $data['locale'];
        $updates = $data['updates'];

        $filePath = $this->getThemeSettingsFilePath($theme, $channel, $locale);

        // Load existing settings
        $existingSettings = $this->loadExistingSettings($theme, $channel, $locale);

        // Apply partial updates using dot notation
        foreach ($updates as $key => $value) {
            data_set($existingSettings, $key, $value);
        }

        // Save merged settings in flattened format
        $this->saveThemeSettings($existingSettings, $filePath, $theme);

        // Clear cache
        $this->clearCache($theme, $channel, $locale);

        return [
            'success' => true,
            'message' => 'Theme settings updated successfully',
        ];
    }

    protected function getThemeSettingsFilePath(string $theme, string $channel, string $locale): string
    {
        return ThemePathsResolver::resolvePath($theme, $channel, $locale, 'editor', 'theme.json');
    }

    protected function saveThemeSettings(array $settings, string $filePath, string $theme): void
    {
        File::ensureDirectoryExists(dirname($filePath));

        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        File::put($filePath, $json);

        // Mark that edits have been made
        $lastEditFile = ThemePathsResolver::getThemeBaseDataPath($theme, 'editor/.last-edit');
        File::put($lastEditFile, (string) time());
    }

    protected function loadExistingSettings(string $themeCode, string $channel, string $locale): array
    {
        // Get theme config
        $themeConfig = config("themes.shop.{$themeCode}");

        if (! $themeConfig) {
            return [];
        }

        $theme = \BagistoPlus\Visual\Theme\Theme::make($themeConfig);

        // Try to find existing settings file using fallback hierarchy
        $settingsPath = ThemePathsResolver::resolveThemeSettingsPath(
            themeCode: $themeCode,
            channel: $channel,
            locale: $locale,
            mode: 'editor'
        );

        // If settings file exists, load it
        if ($settingsPath) {
            $data = $this->themeSettingsLoader->loadFileContent($settingsPath);

            // Support both old nested format {"settings": {...}} and new flattened format {...}
            return $data['settings'] ?? $data;
        }

        // No settings file exists - extract defaults from theme settings schema
        $settingsSchema = collect($theme->settingsSchema)
            ->map(fn ($group) => $group['settings'])
            ->flatten(1)
            ->reject(fn ($schema) => $schema['type'] === 'header')
            ->keyBy('id')
            ->toArray();

        return collect($settingsSchema)->mapWithKeys(function ($schema) {
            return [
                $schema['id'] => $schema['default'] ?? null,
            ];
        })->toArray();
    }

    protected function clearCache(string $theme, string $channel, string $locale): void
    {
        $cacheKey = "theme_settings.{$theme}.{$channel}.{$locale}";
        Cache::forget($cacheKey);
    }
}
