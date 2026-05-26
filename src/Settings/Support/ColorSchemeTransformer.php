<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Theme\Theme;

class ColorSchemeTransformer
{
    public function __invoke(?string $colorScheme = null, array $schema = [])
    {
        if (! $colorScheme) {
            return null;
        }

        /** @var Theme $theme */
        $theme = themes()->current();

        $schemesSetting = collect($theme->settingsSchema)
            ->flatMap(fn ($group) => $group['settings'])
            ->first(fn ($setting) => $setting['type'] === 'color_scheme_group');

        if (! $schemesSetting) {
            return null;
        }

        $schemes = $theme->settings->get($schemesSetting['id']);

        if (! isset($schemes[$colorScheme])) {
            return null;
        }

        // For editor context, return just the ID
        if (request()->is('admin/visual/editor*')) {
            return $colorScheme;
        }

        return $schemes[$colorScheme];
    }
}
