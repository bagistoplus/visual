<?php

namespace BagistoPlus\Visual\Persistence;

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
    public function forget(string $themeCode, string $channel, string $locale): void
    {
        Cache::forget("theme_settings.{$themeCode}.{$channel}.{$locale}");
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
