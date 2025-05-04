<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Sections\Section;
use BagistoPlus\Visual\Sections\SectionInterface;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class VisualManager
{
    public function __construct(protected ThemeDataCollector $themeDataCollector) {}

    public function themeDataCollector(): ThemeDataCollector
    {
        return $this->themeDataCollector;
    }

    /**
     * Discover sections in the given path.
     */
    public function discoverSectionsIn(string $path, string $vendorPreix = ''): void
    {
        if (! File::isDirectory($path)) {
            return;
        }

        $finder = new Finder;
        $finder->files()->in($path)->name('*.php');

        foreach ($finder as $file) {
            $class = $this->extractFullyQualifiedClassName($file->getRealPath());

            if (! $class || ! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if (
                $reflection->implementsInterface(SectionInterface::class) &&
                $reflection->isInstantiable()
            ) {
                $this->registerSection($class, $vendorPreix);
            }
        }
    }

    /**
     * Register a section with the given component class and vendor prefix.
     */
    public function registerSection(string $componentClass, string $vendorPrefix = ''): void
    {
        $section = Section::createFromComponent($componentClass);
        $slug = $section->slug;

        if ($vendorPrefix) {
            $section->slug = $vendorPrefix.'::'.$section->slug;
        }

        Sections::add($section);

        if ($section->isLivewire) {
            Livewire::component("visual-section-{$vendorPrefix}-$slug", $componentClass);
        } else {
            Blade::component($componentClass, $slug, "visual-section-{$vendorPrefix}");
        }
    }

    /**
     * Register multiple sections with the given component classes and vendor prefix.
     */
    public function registerSections(array $sections, string $vendorPrefix = ''): void
    {
        foreach ($sections as $section) {
            $this->registerSection($section, $vendorPrefix);
        }
    }

    /**
     * Collect section data for the given section ID.
     *
     * @param  string|null  $renderPath  path to the  json view file
     */
    public function collectSectionData(string $sectionId, ?string $renderPath = null, ?string $type = null): void
    {
        $this->themeDataCollector->collectSectionData($sectionId, $renderPath, $type);
    }

    /**
     * Check if a section is enabled.
     *
     * @param  string  $sectionId
     */
    public function isSectionEnabled($sectionId): bool
    {
        return ! $this->themeDataCollector->getSectionData($sectionId)->disabled;
    }

    protected function extractFullyQualifiedClassName(string $path): ?string
    {
        $contents = File::get($path);

        if (! preg_match('/^namespace\s+(.+?);/m', $contents, $nsMatch)) {
            return null;
        }

        if (! preg_match('/^class\s+([^\s]+)/m', $contents, $classMatch)) {
            return null;
        }

        return trim($nsMatch[1]).'\\'.trim($classMatch[1]);
    }
}
