<?php

namespace BagistoPlus\Visual\Settings\Support;

class ColorSchemeGroupTransformer
{
    public function __invoke(array $colorSchemes = [], array $schema = [])
    {
        return collect($colorSchemes)->map(function ($colors, $id) {
            return new ColorSchemeValue($id, $colors);
        });
    }
}
