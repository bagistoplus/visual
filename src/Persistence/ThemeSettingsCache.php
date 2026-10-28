<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Facades\Visual;
use Illuminate\Support\Facades\Cache;

/**
 * The cache the storefront reads a theme's settings through.
 *
 * Settings are cached per theme, channel and locale for a day, so anything that
 * changes what is published has to say so or the storefront keeps serving what
 * was there before.
 */
class ThemeSettingsCache
{
    /**
     * The key a theme's settings are cached under for a channel and locale,
     * inside the storage scope when one is set.
     */
    public static function key(string $themeCode, string $channel, string $locale): string
    {
        $scope = Visual::storageScope();
        $key = "theme_settings.{$themeCode}.{$channel}.{$locale}";

        return $scope === '' ? $key : "{$scope}:{$key}";
    }

    public function forget(string $themeCode, string $channel, string $locale): void
    {
        Cache::forget(static::key($themeCode, $channel, $locale));
    }

    /**
     * Forget every channel and locale a theme is read under, for a change that
     * is not made against one of them in particular.
     */
    public function forgetAll(string $themeCode): void
    {
        foreach (core()->getAllChannels() as $channel) {
            foreach ($channel->locales as $locale) {
                $this->forget($themeCode, $channel->code, $locale->code);
            }
        }
    }
}
