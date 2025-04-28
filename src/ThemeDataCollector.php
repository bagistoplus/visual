<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Sections\Concerns\SectionData;
use BagistoPlus\Visual\Sections\Concerns\SettingsValues;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;

class ThemeDataCollector
{
    /**
     * Collection of section data.
     *
     * @var Collection<string, SectionData>
     */
    protected $sectionsData;

    /**
     * Cache for loaded file contents.
     *
     * @var array<string, mixed>
     */
    protected $cache = [];

    /**
     * Create a new ThemeDataCollector instance.
     *
     * @param  Filesystem  $files  The filesystem instance for file operations.
     */
    public function __construct(protected ThemePathsResolver $themePathsResolver, protected Filesystem $files)
    {
        $this->sectionsData = collect();
    }

    /**
     * Get all collected section data.
     *
     * @return Collection<string, SectionData>
     */
    public function getSectionsData(): Collection
    {
        return $this->sectionsData;
    }

    public function setSectionData($id, SectionData $data)
    {
        $this->sectionsData->put($id, $data);
    }

    /**
     * Get data for a specific section by ID.
     *
     * @param  string  $sectionId  The ID of the section.
     */
    public function getSectionData(string $sectionId): ?SectionData
    {
        return $this->sectionsData->get($sectionId);
    }

    /**
     * Get Theme Settings
     */
    public function getThemeSettings(): SettingsValues
    {
        if (! ($theme = app('themes')->current())) {
            return new SettingsValues;
        }

        $dataPath = $this->getDefaultDataFilePath();
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

        return new SettingsValues($settings, $settingsSchema);
    }

    /**
     * Collect data for a specific section.
     *
     * @param  string  $sectionId  The ID of the section.
     * @param  string|null  $dataFilePath  Optional path to the data file.
     */
    public function collectSectionData(string $sectionId, ?string $dataFilePath = null): void
    {
        if ($dataFilePath === null) {
            $dataFilePath = $this->getDefaultDataFilePath();
        }

        $data = $this->collectSectionDataFromPath($sectionId, $dataFilePath);
        $section = Sections::get($data['type'] ?? $sectionId);
        $data['id'] = $sectionId;
        $data['name'] = $section->name;

        if (! isset($data['settings']) && isset($section->default)) {
            $data['settings'] = $section->default['settings'] ?? [];
        }

        if (! isset($data['blocks']) && isset($section->default)) {
            $data['blocks'] = collect($section->default['blocks'] ?? [])
                ->mapWithKeys(function ($block) {
                    return [$block['type'] => $block];
                })
                ->toArray();

            $data['blocks_order'] = array_keys($data['blocks']);
        }

        $this->sectionsData->put($sectionId, SectionData::make(
            id: $sectionId,
            data: $data,
            section: $section,
            sourceFile: $dataFilePath
        ));
    }

    /**
     * Collect section data from a specific file path.
     *
     * @param  string  $sectionId  The ID of the section.
     * @param  string  $path  The path to the data file.
     * @return array<string, mixed>
     */
    protected function collectSectionDataFromPath($sectionId, $path)
    {
        $data = $this->loadFileContent($path);

        return Arr::get($data, "sections.$sectionId");
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

    public function getDefaultDataFilePath(): ?string
    {
        $mode = ThemeEditor::inDesignMode() ? 'editor' : 'live';
        $themeCode = app('themes')->current()->code;
        $channel = app('core')->getRequestedChannelCode();
        $locale = app('core')->getRequestedLocaleCode();

        $path = $this->themePathsResolver->resolvePath($themeCode, $channel, $locale, $mode, 'theme.json');

        if ($this->files->exists($path)) {
            return $path;
        }

        return $this->themePathsResolver->resolveThemeFallbackDataPath(
            themeCode: app('themes')->current()->code,
            channel: app('core')->getRequestedChannelCode(),
            locale: app('core')->getRequestedLocaleCode(),
            mode: $mode
        );
    }

    public function getThemeDataFilePath() {}
}
