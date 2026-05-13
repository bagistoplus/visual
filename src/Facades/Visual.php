<?php

namespace BagistoPlus\Visual\Facades;

use BagistoPlus\Visual\VisualManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \BagistoPlus\Visual\ThemeDataCollector themeDataCollector()
 * @method static void discoverSectionsIn(string $path, string $vendorPreix = '')
 * @method static void registerSection(string $componentClass, string $vendorPrefix = '')
 * @method static void registerSections(array $sections, string $vendorPrefix = '')
 * @method static void collectSectionData(string $sectionId, string|null $renderPath = null, string|null $type = null)
 * @method static bool isSectionEnabled(string $sectionId)
 *
 * @see VisualManager
 */
class Visual extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return VisualManager::class;
    }
}
