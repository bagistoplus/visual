<?php

namespace BagistoPlus\Visual\Sections\Concerns;

class ColorSchemeGroupTransformer
{
    public function __invoke(array $colorSchemes = [])
    {
        return collect($colorSchemes)->map(function ($colors, $id) {
            return (new ColorScheme($id, $colors));
        });
    }
}
