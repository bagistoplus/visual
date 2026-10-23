<?php

namespace BagistoPlus\Visual\Settings\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class TypographyPresetCollection extends Collection
{
    public function fontLinks(): HtmlString
    {
        $fonts = [];

        foreach ($this->items as $preset) {
            if (! $preset instanceof TypographyValue || ! $preset->fontFamily) {
                continue;
            }

            $font = $preset->fontFamily;

            $fonts[$font->slug]['name'] = $font->name;
            $fonts[$font->slug]['weights'][$preset->fontWeight] = true;
            $fonts[$font->slug]['styles'][$preset->fontStyle] = true;
        }

        return FontValue::linksFor(array_map(
            fn (array $font, string $slug) => new FontValue(
                slug: $slug,
                name: $font['name'],
                weights: array_keys($font['weights']),
                styles: array_keys($font['styles']),
            ),
            $fonts,
            array_keys($fonts),
        ));
    }
}
