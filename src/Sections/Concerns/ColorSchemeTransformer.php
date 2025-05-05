<?php

namespace BagistoPlus\Visual\Sections\Concerns;

class ColorSchemeTransformer
{
    public function __invoke(?string $colorScheme = null)
    {
        if (! $colorScheme) {
            return null;
        }

        return new ColorScheme($colorScheme);
    }
}
