<?php

namespace BagistoPlus\Visual\Settings\Support;

class ColorSchemeTransformer
{
    public function __invoke(?string $colorScheme = null)
    {
        if (! $colorScheme) {
            return null;
        }

        return new ColorSchemeValue($colorScheme);
    }
}
