<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Facades\ThemeEditor;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;

class ThemeDataCollector
{
    /**
     * Undocumented variable
     *
     * @var Collection<string, array>
     */
    protected $sectionsData;

    public function __construct(protected Filesystem $files)
    {
        $this->sectionsData = collect();
    }

    public function getSectionsData()
    {
        return $this->sectionsData;
    }

    public function getSectionData(string $sectionId)
    {
        return $this->sectionsData->get($sectionId);
    }

    public function collectSectionData(string $sectionId, ?string $dataFilePath = null): void
    {
        // dd($dataFilePath);
        if ($dataFilePath === null) {
            $dataFilePath = $this->getDefaulDataFilePath(
                mode: ThemeEditor::inDesignMode() ? 'editor' : 'live'
            );
        }

        $data = $this->collectSectionDataFromPath($sectionId, $dataFilePath);
        $section = Sections::get($data['type'] ?? $sectionId);
        $data['id'] = $sectionId;
        $data['name'] = $section->name;

        collect($section->settings)->whereNotIn('type', ['header'])
            ->each(function ($setting) use (&$data) {
                if (! isset($data['settings'][$setting['id']])) {
                    $data['settings'][$setting['id']] = $setting['default'] ?? null;
                }
            });

        $this->sectionsData->put($sectionId, $data);
    }

    protected function collectSectionDataFromPath($sectionId, $path)
    {
        $data = $this->loadFileContent($path);

        return Arr::get($data, "sections.$sectionId", [
            'settings' => [],
            'blocks' => [],
            'block_order' => [],
        ]);
    }

    protected function loadFileContent($path)
    {
        if (! $this->files->exists($path)) {
            return [];
        }

        if (pathinfo($path, PATHINFO_EXTENSION) === 'json') {
            return json_decode($this->files->get($path), true);
        } else {
            return Yaml::parseFile($path);
        }
    }

    protected function getDefaulDataFilePath(string $mode): string
    {
        return strtr(
            '%data_path/themes/%theme_code/%mode/%channel/%locale/theme.json',
            [
                '%data_path' => rtrim(config('bagisto_visual.data_path'), '/\\'),
                '%theme_code' => app('themes')->current()->code,
                '%channel' => app('core')->getRequestedChannelCode(),
                '%locale' => app('core')->getRequestedLocaleCode(),
                '%mode' => $mode,
            ]
        );
    }
}
