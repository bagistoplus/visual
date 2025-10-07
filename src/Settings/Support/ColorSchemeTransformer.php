<?php

namespace BagistoPlus\Visual\Settings\Support;

class ColorSchemeTransformer
{
    public function __invoke(?string $colorScheme = null)
    {
        if (! $colorScheme) {
            return null;
        }

        // For editor context, return just the ID
        if (request()->is('admin/visual/editor*')) {
            return $colorScheme;
        }

        return new ColorSchemeValue($colorScheme);
    }
}
