<?php

namespace BagistoPlus\Visual\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \BagistoPlus\Visual\ThemeDataCollector themeDataCollector()
 * @method static void collectSectionData(string $sectionId, string|null $renderPath = null)
 * @method static void registerSection(string $componentClass, string $prefix)
 * @method static array getVisualThemePaths(string $theme)
 *
 * @see \BagistoPlus\Visual\Visual
 */
class Visual extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BagistoPlus\Visual\Visual::class;
    }
}
