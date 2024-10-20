<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Sections\Section;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Swaggest\JsonSchema\Schema;

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
        $data = json_decode(file_get_contents($schemaPath));
        $schema = json_decode(file_get_contents(__DIR__.'/../resources/schemas/section.schema.json'));

        $validator = Schema::import($schema);
        $validator->in($data);
    }

    public function isSectionEnabled($sectionId): bool
    {
        return ! $this->themeDataCollector->getSectionData($sectionId)->disabled;
    }

    public function getVisualThemePaths(string $themeCode): array
    {
        $mode = ThemeEditor::inDesignMode() ? 'editor' : 'live';

        $requestedChannel = app('core')->getRequestedChannel();
        $defaultChannel = app('core')->getDefaultChannel();
        $requestedLocale = app('core')->getRequestedLocale();

        $paths = [
            $this->buildThemePath($themeCode, $mode, $requestedChannel->code, $requestedLocale->code),
        ];

        if ($requestedLocale->code !== $requestedChannel->default_locale->code) {
            $paths[] = $this->buildThemePath($themeCode, $mode, $requestedChannel->code, $requestedChannel->default_locale->code);
        }

        if ($requestedChannel->code !== $defaultChannel->code) {
            $paths[] = $this->buildThemePath($themeCode, $mode, $defaultChannel->code, $requestedLocale->code);

            if ($requestedLocale->code !== $defaultChannel->default_locale->code) {
                $paths[] = $this->buildThemePath($themeCode, $mode, $defaultChannel->code, $defaultChannel->default_locale->code);
            }
        }

        // Bagisto prepend base path to the provided paths
        // so we strip it
        return array_map(fn ($p) => substr($p, strlen(base_path()) + 1), $paths);
    }

    public function buildThemePath(string $themeCode, $mode, $channel, $locale)
    {
        return strtr(
            '%data_path/%channel/%locale',
            [
                '%data_path' => $this->getThemeBaseDataPath($themeCode, $mode),
                '%channel' => $channel,
                '%locale' => $locale,
            ]
        );
    }

    public function getThemeBaseDataPath(string $themeCode, string $mode = 'live')
    {
        return strtr('%data_path/themes/%theme_code/%mode', [
            '%data_path' => rtrim(config('bagisto_visual.data_path'), '/\\'),
            '%theme_code' => $themeCode,
            '%mode' => $mode,
        ]);
    }
}
