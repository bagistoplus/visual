<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Sections\Section;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;

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
        $schemaPath = $componentClass::getSchemaPath();

        if ($this->shouldValidateSectionSchema($schemaPath)) {
            $this->validateSectionSchema($schemaPath);
            Cache::forever($schemaPath, [
                'validated' => true,
                'last_modified' => filemtime($schemaPath),
            ]);
        }

        $section = Section::createFromComponent($componentClass);
        $section->slug = $prefix.'-'.$section->slug;

        Sections::add($section);

        Blade::component($componentClass, $section->slug, 'visual-section');
    }

    protected function shouldValidateSectionSchema(string $schemaPath): bool
    {
        $cached = Cache::get($schemaPath);
        $lastModified = filemtime($schemaPath);

        return ! $cached || $cached['last_modified'] < $lastModified;
    }

    protected function validateSectionSchema(string $schemaPath): void
    {
        JsonSchemaValidator::validateSectionSchema($schemaPath);
    }

    public function isSectionEnabled($sectionId): bool
    {
        return ! $this->themeDataCollector->getSectionData($sectionId)->disabled;
    }
}
