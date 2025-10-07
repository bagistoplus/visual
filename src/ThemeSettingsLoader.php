<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Theme\Theme;
use Craftile\Laravel\PropertyBag;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Yaml\Yaml;

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

        // Skip cache in design mode or if cache is disabled
        if (ThemeEditor::inDesignMode() || $cacheTtl <= 0) {
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

        $settings = collect($settingsSchema)->mapWithKeys(function ($schema) use ($data) {
            return [
                $schema['id'] => $data['settings'][$schema['id']] ?? $schema['default'] ?? null,
            ];
        })->toArray();

        return new PropertyBag($settings, $settingsSchema);
    }

    /**
     * Load file content from a specified path.
     *
     * @param  string  $path  The path to the file.
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

        if (pathinfo($path, PATHINFO_EXTENSION) === 'json') {
            $content = $this->loadJsonDataFile($path);
        } else {
            $content = Yaml::parseFile($path);
        }

        return $this->cache[$path] = $content;
    }

    public function loadJsonDataFile(string $path)
    {
        $data = json_decode($this->files->get($path), true);

        if (isset($data['parent']) && ! empty($data['parent'])) {
            $parentPath = config('bagisto_visual.data_path').DIRECTORY_SEPARATOR.$data['parent'];
            $parentData = $this->loadFileContent($parentPath);
            unset($data['parent']);

            return $this->mergeRecursively($data, $parentData);
        }

        return $data;
    }

    /**
     * Recursively merge the a json template data with its parent data.
     *
     * Special treatment is applied to the 'order' and 'blocks_order' keys.
     * - 'order' represents the sections order and should always override the parent value
     *   if changed in the child data.
     * - 'blocks_order' should also override the parent value if changed in the child data.
     *
     * @param  array  $current  The current template data.
     * @param  array  $parent  The parent template data.
     * @return array The merged data with values from the current data overriding the parent data.
     */
    protected function mergeRecursively(array $current, array $parent): array
    {
        $merged = $parent;

        foreach ($current as $key => $value) {
            if ($key === 'order' || $key === 'blocks_order') {
                $merged[$key] = $value;
            } elseif (is_array($value) && isset($parent[$key]) && is_array($parent[$key])) {
                $merged[$key] = $this->mergeRecursively($value, $parent[$key]);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Get the settings file path for a specific theme.
     */
    public function getThemeSettingsFilePath(string $themeCode): ?string
    {
        $mode = ThemeEditor::inDesignMode() ? 'editor' : 'live';
        $channel = core()->getRequestedChannelCode();
        $locale = core()->getRequestedLocaleCode();

        $path = $this->themePathsResolver->resolvePath($themeCode, $channel, $locale, $mode, 'theme.json');

        if ($this->files->exists($path)) {
            return $path;
        }

        return $this->themePathsResolver->resolveThemeFallbackDataPath(
            themeCode: $themeCode,
            channel: $channel,
            locale: $locale,
            mode: $mode
        );
    }
}
