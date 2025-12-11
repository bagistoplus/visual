<?php

namespace BagistoPlus\Visual\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \BagistoPlus\Visual\ThemeSettingsLoader themeSettingsLoader()
 * @method static void discoverSectionsIn(string $path, string $namespace = 'App\\Sections')
 * @method static void discoverBlocksIn(string $path, string $namespace = 'App\\Blocks')
 * @method static void registerSection(string $sectionClass)
 * @method static void registerSections(array $sectionClasses)
 * @method static void registerBlock(string $blockClass)
 * @method static void registerBlocks(array $blockClasses)
 * @method static void registerSettingTransformer(string $type, \BagistoPlus\Visual\Contracts\SettingTransformerInterface $transformerClass)
 * @method static void filterLivewireContextUsing(callable $filter)
 * @method static array getLivewireContextFilters()
 * @method static void supportLivewire()
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
