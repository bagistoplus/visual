<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Persistence\Data\ThemeSettingsUpdateData;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Illuminate\Support\Facades\Cache;

class PersistThemeSettings
{
    public function __construct(
        protected ThemeSettingsLoader $themeSettingsLoader,
        protected EditorDataStore $editorDataStore,
        protected ThemeSettingsDiffer $themeSettingsDiffer,
        protected LocalizedProperties $localizedProperties,
    ) {}

    public function handle(ThemeSettingsUpdateData $data): array
    {
        $relativePath = $this->editorDataStore->relativePath($data->channel, $data->locale, 'theme.json');

        // Load existing settings
        $existingSettings = $this->editorDataStore->loadResolved($data->theme, $relativePath)
            ?: $this->loadExistingSettings($data->theme, $data->channel, $data->locale);

        // Apply partial updates using dot notation
        foreach ($data->updates as $key => $value) {
            data_set($existingSettings, $key, $value);
        }

        $parent = $this->selectParent($data->theme, $data->channel, $data->locale, $relativePath);
        $parentData = $parent ? $this->editorDataStore->loadResolved($data->theme, $parent) : [];
        $clean = $this->themeSettingsDiffer->clean($existingSettings, $parentData);
        $diff = $parent ? $this->editorDataStore->diff($clean, $parentData) : $clean;

        if ($parent && $this->editorDataStore->localeFromRelative($parent) !== $data->locale) {
            $themeConfig = config("themes.shop.{$data->theme}");
            $schema = $themeConfig ? Theme::make($themeConfig)->settingsSchema : [];
            $diff = $this->editorDataStore->merge($diff, $this->localizedProperties->themeSettingsFragment($clean, $schema));
        }

        $this->editorDataStore->save($data->theme, $relativePath, $diff, $parent);

        // Clear cache
        $this->clearCache($data->theme, $data->channel, $data->locale);

        return [
            'success' => true,
            'message' => 'Theme settings updated successfully',
        ];
    }

    protected function selectParent(string $theme, string $channel, string $locale, string $relativePath): ?string
    {
        return $this->editorDataStore->storedParent($theme, $relativePath)
            ?? $this->editorDataStore->nearestFallbackParent($theme, $channel, $locale, 'theme.json');
    }

    protected function loadExistingSettings(string $themeCode, string $channel, string $locale): array
    {
        // Get theme config
        $themeConfig = config("themes.shop.{$themeCode}");

        if (! $themeConfig) {
            return [];
        }

        $theme = Theme::make($themeConfig);

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
