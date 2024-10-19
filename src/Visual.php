<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Sections\Section;
use Illuminate\Support\Facades\Blade;

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

        Blade::component($componentClass, $section->slug, 'visual-section');
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
