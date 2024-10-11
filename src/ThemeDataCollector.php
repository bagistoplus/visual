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
     * Undocumented variable
     *
     * @var Collection<string, SectionData>
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
        if ($dataFilePath === null) {
            $dataFilePath = $this->getDefaulDataFilePath();
        }

        $data = $this->collectSectionDataFromPath($sectionId, $dataFilePath);
        $section = Sections::get($data['type'] ?? $sectionId);
        $data['id'] = $sectionId;
        $data['name'] = $section->name;

        $this->sectionsData->put($sectionId, SectionData::make($sectionId, $data, $section));
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

    protected function getDefaulDataFilePath(): string
    {
        $mode = ThemeEditor::inDesignMode() ? 'editor' : 'live';

        return Visual::buildThemePath(
            theme: app('themes')->current()->code,
            mode: $mode,
            channel: app('core')->getRequestedChannelCode(),
            locale: app('core')->getRequestedLocaleCode()
        ).'/theme.json';
    }
}
