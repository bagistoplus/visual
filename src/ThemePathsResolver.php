<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Webkul\Core\Models\Channel;

class ThemePathsResolver
{
    /**
     * Resolve a specific data path within a theme for a given channel and locale.
     *
     * @param  string  $themeCode  The code of the theme.
     * @param  string  $channel  The code of the channel.
     * @param  string  $locale  The locale of the theme.
     * @param  string  $mode  Mode of the theme ('live', 'editor', etc.).
     * @param  string  $path  Optional specific file or directory to resolve within the theme.
     * @return string The fully resolved path.
     */
    public function resolvePath(string $themeCode, string $channel, string $locale, string $mode, string $path = ''): string
    {
        $basePath = $this->buildThemePath($themeCode, $mode, $channel, $locale);

        if (! empty($path)) {
            return $basePath.'/'.ltrim($path, '/');
        }

        return $basePath;
    }

    /**
     * Resolve the path to theme settings file based on the channel and locale hierarchy.
     *
     * @param  string  $themeCode  The code of the theme.
     * @param  string  $channel  The code of the current channel.
     * @param  string  $locale  The locale for the current channel.
     * @param  string  $mode  The mode of the theme ('live', 'editor', etc.). Defaults to 'live'.
     * @return string|null The resolved settings path or null if no path exists.
     */
    public function resolveThemeSettingsPath(string $themeCode, string $channel, string $locale, string $mode = 'live'): ?string
    {
        /** @var \Webkul\Core\Models\Channel */
        $defaultChannel = core()->getDefaultChannel();

        /** @var \Webkul\Core\Models\Channel */
        $channelModel = once(fn () => Channel::query()->with(['default_locale'])->where('code', $channel)->first());
        $pathsToCheck = [];

        if ($channel !== $defaultChannel->code) {
            if ($locale !== $channelModel->default_locale->code) {
                $pathsToCheck[] = $this->buildThemePath($themeCode, $mode, $channel, $channelModel->default_locale->code);
            }

            $pathsToCheck[] = $this->buildThemePath($themeCode, $mode, $defaultChannel->code, $locale);
        }

        $pathsToCheck[] = $this->buildThemePath($themeCode, $mode, $defaultChannel->code, $defaultChannel->default_locale->code);

        foreach ($pathsToCheck as $path) {
            $path = $path.'/theme.json';

            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Resolve all possible paths where theme views can be found.
     *
     * @param  string  $themeCode  The code of the theme.
     * @return array Array of possible view paths.
     */
    public function resolveThemeViewsPaths(string $themeCode): array
    {
        $mode = ThemeEditor::active() ? 'editor' : 'live';

        /** @var \Webkul\Core\Models\Channel $requestedChannel */
        $requestedChannel = core()->getRequestedChannel();

        /** @var \Webkul\Core\Models\Channel $defaultChannel */
        $defaultChannel = core()->getDefaultChannel();

        $requestedLocale = app()->getLocale();

        $paths = [
            $this->buildThemePath($themeCode, $mode, $requestedChannel->code, $requestedLocale),
        ];

        if ($requestedLocale !== $requestedChannel->default_locale->code) {
            $paths[] = $this->buildThemePath($themeCode, $mode, $requestedChannel->code, $requestedChannel->default_locale->code);
        }

        if ($requestedChannel->code !== $defaultChannel->code) {
            $paths[] = $this->buildThemePath($themeCode, $mode, $defaultChannel->code, $requestedLocale);

            if ($requestedLocale !== $defaultChannel->default_locale->code) {
                $paths[] = $this->buildThemePath($themeCode, $mode, $defaultChannel->code, $defaultChannel->default_locale->code);
            }
        }

        // Bagisto prepend base path to the provided paths
        // so we strip it
        return array_map(fn ($p) => substr($p, strlen(base_path()) + 1), $paths);
    }

    /**
     * Build the base theme path based on theme code, channel, locale, and mode.
     *
     * @param  string  $themeCode  The code of the theme.
     * @param  string  $mode  The mode of the theme ('live', 'editor', etc.).
     * @param  string  $channel  The code of the channel.
     * @param  string  $locale  The locale of the theme.
     * @return string The fully constructed theme path.
     */
    public function buildThemePath(string $themeCode, $mode, $channel, $locale): string
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

    /**
     * Get the base data path for the theme.
     *
     * @param  string  $themeCode  The code of the theme.
     * @param  string  $mode  The mode of the theme ('live', 'editor', etc.).
     * @return string The base data path for the theme.
     */
    public function getThemeBaseDataPath(string $themeCode, string $mode = 'live'): string
    {
        return strtr('%data_path/themes/%theme_code/%mode', [
            '%data_path' => rtrim(config('bagisto_visual.data_path'), '/\\'),
            '%theme_code' => $themeCode,
            '%mode' => $mode,
        ]);
    }
}
