<?php

namespace BagistoPlus\Visual\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \BagistoPlus\Visual\ThemeSettingsLoader themeSettingsLoader()
 * @method static void discoverSectionsIn(string $path, string $namespace = 'App\\Sections')
 * @method static void discoverBlocksIn(string $path, string $namespace = 'App\\Blocks')
 * @method static void registerSettingTransformer(string $type, string $transformerClass)
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
