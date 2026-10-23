<?php

namespace BagistoPlus\Visual\Settings\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class TypographyPresetCollection extends Collection
{
    /**
     * The links that load every font the presets use, in the weights and styles they use.
     *
     * A theme can include more weights for each font, such as the ones its templates set
     * with weight classes. Browsers only download a weight when text on the page uses it.
     *
     * @param  list<string|int>  $includeWeights
     */
    public function fontLinks(array $includeWeights = []): HtmlString
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

        foreach ($fonts as $slug => $font) {
            foreach ($includeWeights as $weight) {
                $fonts[$slug]['weights'][$weight] = true;
            }
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
