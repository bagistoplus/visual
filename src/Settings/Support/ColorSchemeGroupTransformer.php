<?php

namespace BagistoPlus\Visual\Settings\Support;

class ColorSchemeGroupTransformer
{
    public function __invoke(array $colorSchemes = [])
    {
        return collect($colorSchemes)->map(function ($colors, $id) {
            return new ColorScheme($id, $colors);
        });
    }
}
