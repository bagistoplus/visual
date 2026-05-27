<?php

namespace BagistoPlus\Visual\Settings\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class TypographyPresetCollection extends Collection
{
    public function fontLinks(): HtmlString
    {
        $fonts = [];
        $preconnect = '<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>';

        foreach ($this->items as $preset) {
            if (! $preset instanceof TypographyValue || ! $preset->fontFamily) {
                continue;
            }

            $font = $preset->fontFamily;

            $fonts[$font->slug]['name'] = $font->name;
            $fonts[$font->slug]['weights'][$preset->fontWeight] = true;
            $fonts[$font->slug]['styles'][$preset->fontStyle] = true;
        }

        if ($fonts === []) {
            return new HtmlString('');
        }

        $html = [];

        foreach ($fonts as $slug => $font) {
            $fontHtml = (new FontValue(
                slug: $slug,
                name: $font['name'],
                weights: array_keys($font['weights']),
                styles: array_keys($font['styles']),
            ))->toHtml();

            $html[] = str_replace($preconnect."\n  ", '', (string) $fontHtml);
        }

        return new HtmlString($preconnect."\n".implode("\n", $html));
    }
}
