<?php

namespace BagistoPlus\Visual\Settings\Support;

use Illuminate\Support\Facades\Storage;

class ImageTransformer
{
    public function __invoke(?string $path = null)
    {
        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return new ImageValue(
                name: '',
                path: $path,
                url: $path
            );
        }

        [$encodedName] = explode('_', pathinfo($path, PATHINFO_FILENAME));

        // check if it is hex string
        // @see https://stackoverflow.com/questions/41194159/how-to-catch-hex2bin-warning
        if (ctype_xdigit($encodedName) && strlen($encodedName) % 2 == 0) {
            $originalName = hex2bin($encodedName);

            return new ImageValue(
                name: $originalName,
                path: $path,
                url: Storage::disk(config('bagisto_visual.images_storage'))->url($path)
            );
        }

        return new ImageValue(
            name: $encodedName,
            path: $path,
            url: url($path)
        );
    }
}
