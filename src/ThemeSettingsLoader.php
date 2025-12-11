<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Theme\Theme;
use Craftile\Laravel\PropertyBag;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;

class ThemeSettingsLoader
{
    /**
     * Cache for loaded file contents.
     *
     * @var array<string, mixed>
     */
    protected $cache = [];

    /**
     * Create a new ThemeSettingsLoader instance.
     *
     * @param  Filesystem  $files  The filesystem instance for file operations.
     */
    public function __construct(protected ThemePathsResolver $themePathsResolver, protected Filesystem $files) {}

    /**
     * Load settings for the currently active theme.
     */
    public function loadActiveThemeSettings(): PropertyBag
    {
        /** @var \BagistoPlus\Visual\Theme\Theme|null $theme */
        $theme = themes()->current();

        if (! $theme) {
            return new PropertyBag;
        }

        return $this->loadThemeSettings($theme);
    }

    /**
     * Load settings for a specific theme.
     */
    public function loadThemeSettings(Theme $theme): PropertyBag
    {
        $cacheTtl = config('bagisto_visual.settings_cache_ttl', 86400);

        // Skip cache in design mode, editor routes, or if cache is disabled
        if (ThemeEditor::active() || $cacheTtl <= 0) {
            return $this->loadThemeSettingsFromFile($theme);
        }

        $channel = core()->getRequestedChannelCode();
        $locale = core()->getRequestedLocaleCode();
        $cacheKey = "theme_settings.{$theme->code}.{$channel}.{$locale}";

        return Cache::remember($cacheKey, $cacheTtl, function () use ($theme) {
            return $this->loadThemeSettingsFromFile($theme);
        });
    }

    /**
     * Load settings from file without caching.
     */
    protected function loadThemeSettingsFromFile(Theme $theme): PropertyBag
    {
        $dataPath = $this->getThemeSettingsFilePath($theme->code);
        $data = $this->loadFileContent($dataPath);

        $settingsSchema = collect($theme->settingsSchema)
            ->map(fn ($group) => $group['settings'])
            ->flatten(1)
            ->reject(fn ($schema) => $schema['type'] === 'header')
            ->keyBy('id')
            ->toArray();

        $settingsData = $data['settings'] ?? $data;
        $settings = collect($settingsSchema)->mapWithKeys(function ($schema) use ($settingsData) {
            return [
                $schema['id'] => $settingsData[$schema['id']] ?? $schema['default'] ?? null,
            ];
        })->toArray();

        return new PropertyBag($settings, $settingsSchema);
    }

    /**
     * Load JSON file content from a specified path.
     *
     * @param  string  $path  The path to the JSON file.
     * @return array<string, mixed>
     */
    public function loadFileContent($path): array
    {
        if (array_key_exists($path, $this->cache)) {
            return $this->cache[$path];
        }

        if ($path === null || ! $this->files->exists($path)) {
            return [];
        }

        $content = json_decode($this->files->get($path), true);

        return $this->cache[$path] = $content;
    }

    /**
     * Get the settings file path for a specific theme using fallback hierarchy.
     */
    protected function getThemeSettingsFilePath(string $themeCode): ?string
    {
        $mode = ThemeEditor::active() ? 'editor' : 'live';
        $channel = core()->getRequestedChannelCode();
        $locale = core()->getRequestedLocaleCode();

        return $this->themePathsResolver->resolveThemeSettingsPath(
            themeCode: $themeCode,
            channel: $channel,
            locale: $locale,
            mode: $mode
        );
    }

    /**
     * Clear the local file content cache.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
