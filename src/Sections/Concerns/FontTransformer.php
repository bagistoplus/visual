<?php

namespace BagistoPlus\Visual\Sections\Concerns;

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
                'name' => Str::title($font),
            ];
        }

        $font['weights'] = $font['weights'] ?? [];
        $font['styles'] = $font['styles'] ?? [];

        return new Font(
            slug: $font['slug'],
            name: $font['name'],
            weights: $font['weights'],
            styles: $font['styles'],
        );
    }
}
