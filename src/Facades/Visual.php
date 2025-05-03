<?php

namespace BagistoPlus\Visual\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \BagistoPlus\Visual\ThemeDataCollector themeDataCollector()
 * @method static void discoverSectionsIn(string $path, string $vendorPreix = '')
 * @method static void registerSection(string $componentClass, string $vendorPrefix = '')
 * @method static void registerSections(array $sections, string $vendorPrefix = '')
 * @method static void collectSectionData(string $sectionId, string|null $renderPath = null)
 * @method static array isSectionEnabled(string $sectionId)
 *
 * @see \BagistoPlus\Visual\VisualManager
 */
class Visual extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BagistoPlus\Visual\VisualManager::class;
    }
}
