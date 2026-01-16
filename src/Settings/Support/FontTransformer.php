<?php

namespace BagistoPlus\Visual\Settings\Support;

use Illuminate\Support\Str;

class FontTransformer
{
    public function __invoke(string|array|null $font = null)
    {
        if (! $font) {
            return null;
        }

        if (is_string($font)) {
            $font = [
                'slug' => Str::kebab($font),
                'name' => Str::headline($font),
            ];
        }

        $font['weights'] = $font['weights'] ?? ['400'];
        $font['styles'] = $font['styles'] ?? ['normal'];

        return new FontValue(
            slug: $font['slug'],
            name: $font['name'],
            weights: $font['weights'],
            styles: $font['styles'],
        );
    }
}
