<?php

namespace BagistoPlus\Visual\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string resolvePath(string $themeCode, string $channel, string $locale, string $mode, string $path = '')
 * @method static string|null resolveThemeFallbackDataPath(string $themeCode, string $channel, string $locale, string $mode = 'live')
 * @method static array resolveThemeViewsPaths(string $themeCode)
 * @method static string buildThemePath(string $themeCode, string $mode, string $channel, string $locale)
 * @method static string getThemeBaseDataPath(string $themeCode, string $mode = 'live')
 *
 * @see \BagistoPlus\Visual\ThemePathsResolver
 */
class ThemePathsResolver extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BagistoPlus\Visual\ThemePathsResolver::class;
    }
}
