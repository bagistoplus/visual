<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Sections\Section;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

class Visual
{
    public function __construct(protected ThemeDataCollector $themeDataCollector) {}

    public function themeDataCollector(): ThemeDataCollector
    {
        return $this->themeDataCollector;
    }

    public function collectSectionData(string $sectionId, ?string $renderPath = null): void
    {
        $this->themeDataCollector->collectSectionData($sectionId, $renderPath);
    }

    public function registerSection(string $componentClass, string $prefix): void
    {
        $section = Section::createFromComponent($componentClass);
        $section->slug = $prefix.'-'.$section->slug;

        Sections::add($section);

        if ($section->isLivewire) {
            Livewire::component('visual-section-'.$section->slug, $componentClass);
        } else {
            Blade::component($componentClass, $section->slug, 'visual-section');
        }
    }

    public function registerSections(array $sections, string $prefix): void
    {
        foreach ($sections as $section) {
            $this->registerSection($section, $prefix);
        }
    }

    public function isSectionEnabled($sectionId): bool
    {
        return ! $this->themeDataCollector->getSectionData($sectionId)->disabled;
    }
}
