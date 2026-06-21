<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Persistence\EditorDataStore;
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
    public function __construct(
        protected ThemePathsResolver $themePathsResolver,
        protected Filesystem $files,
        protected EditorDataStore $editorDataStore,
    ) {}

    /**
     * Load settings for the currently active theme.
     */
    public function loadActiveThemeSettings(): PropertyBag
    {
        /** @var Theme|null $theme */
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
        $data = $this->loadThemeSettingsFile($theme->code, $dataPath);

        $settingsSchema = collect($theme->settingsSchema)
            ->map(fn ($group) => $group['settings'])
            ->flatten(1)
            ->reject(fn ($schema) => $schema['type'] === 'header')
            ->keyBy('id')
            ->toArray();

        $settingsData = $data['settings'] ?? $data;
        unset($settingsData['parent']);
        $settings = collect($settingsSchema)->mapWithKeys(function ($schema) use ($settingsData) {
            $value = $settingsData[$schema['id']] ?? $schema['default'] ?? null;

            return [
                $schema['id'] => ($schema['localized'] ?? false) === true
                    ? $this->resolveTranslationReferences($value, $schema)
                    : $value,
            ];
        })->toArray();

        return new PropertyBag($settings, $settingsSchema);
    }

    protected function loadThemeSettingsFile(string $themeCode, ?string $path): array
    {
        if ($path === null) {
            return [];
        }

        if (ThemeEditor::active()) {
            $relativePath = $this->editorDataStore->relativePathFromAbsolute($themeCode, $path);

            if ($relativePath) {
                return $this->editorDataStore->loadResolved($themeCode, $relativePath);
            }
        }

        return $this->loadFileContent($path);
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

        return $this->cache[$path] = is_array($content) ? $content : [];
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

    protected function resolveTranslationReferences(mixed $value, array $schema): mixed
    {
        if (is_string($value)) {
            return $this->resolveTranslationReference($value);
        }

        if (is_array($value) && ($schema['responsive'] ?? false) === true) {
            foreach ($value as $key => $item) {
                $value[$key] = is_string($item)
                    ? $this->resolveTranslationReference($item)
                    : $item;
            }
        }

        return $value;
    }

    protected function resolveTranslationReference(string $value): mixed
    {
        if (str_starts_with($value, 't:') && $value !== 't:') {
            return __(substr($value, 2));
        }

        return $value;
    }
}
