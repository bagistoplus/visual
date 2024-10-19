<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Sections\Concerns\SectionData;
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
    public function __construct(protected Filesystem $files)
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

        $this->sectionsData->put($sectionId, SectionData::make($sectionId, $data, $section));
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

        return Arr::get($data, "sections.$sectionId", [
            'settings' => [],
            'blocks' => [],
            'block_order' => [],
        ]);
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

        if (! $this->files->exists($path)) {
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

        if (isset($data['parent'])) {
            $parentPath = config('bagisto_visual.data_path').DIRECTORY_SEPARATOR.$data['parent'];
            $parentData = $this->loadJsonDataFile($parentPath);

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

    protected function getDefaultDataFilePath(): string
    {
        $mode = ThemeEditor::inDesignMode() ? 'editor' : 'live';

        return Visual::buildThemePath(
            themeCode: app('themes')->current()->code,
            mode: $mode,
            channel: app('core')->getRequestedChannelCode(),
            locale: app('core')->getRequestedLocaleCode()
        ).'/theme.json';
    }
}
