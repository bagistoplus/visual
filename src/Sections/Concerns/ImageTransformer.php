<?php

namespace BagistoPlus\Visual\Sections\Concerns;

use Illuminate\Support\Facades\Storage;

class ImageTransformer
{
    public function __invoke(?string $path = null)
    {
        if (! $path) {
            return null;
        }

        [$encodedName] = explode('_', pathinfo($path, PATHINFO_FILENAME));
        $originalName = hex2bin($encodedName);

        return new Image(
            name: $originalName,
            path: $path,
            url: Storage::disk(config('bagisto_visual.images_storage'))->url($path)
        );
    }
}
