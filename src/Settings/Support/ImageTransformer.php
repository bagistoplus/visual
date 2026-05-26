<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\Visual;
use Illuminate\Support\Facades\Storage;

class ImageTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, array $schema = []): ?ImageValue
    {
        return $this($value);
    }

    public function __invoke(mixed $value = null): ?ImageValue
    {
        $metadata = $this->metadata($value);
        $path = $this->path($value);

        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return new ImageValue(
                name: '',
                path: $path,
                url: $path,
                alt: $metadata['alt'],
                focalPoint: $metadata['focalPoint'],
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
                url: Storage::disk(Visual::imagesDisk())->url($path),
                alt: $metadata['alt'],
                focalPoint: $metadata['focalPoint'],
            );
        }

        return new ImageValue(
            name: $encodedName,
            path: $path,
            url: url($path),
            alt: $metadata['alt'],
            focalPoint: $metadata['focalPoint'],
        );
    }

    private function path(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value) && isset($value['path']) && is_string($value['path'])) {
            return $value['path'];
        }

        if (is_object($value) && isset($value->path) && is_string($value->path)) {
            return $value->path;
        }

        return null;
    }

    private function metadata(mixed $value): array
    {
        $metadata = [
            'alt' => '',
            'focalPoint' => ['x' => 50, 'y' => 50],
        ];

        if (! is_array($value) && ! is_object($value)) {
            return $metadata;
        }

        $alt = data_get($value, 'alt');
        $focalPoint = data_get($value, 'focalPoint');

        if (is_string($alt)) {
            $metadata['alt'] = $alt;
        }

        if (is_array($focalPoint) || is_object($focalPoint)) {
            $metadata['focalPoint'] = [
                'x' => $this->normalizeFocalPointValue(data_get($focalPoint, 'x', 50)),
                'y' => $this->normalizeFocalPointValue(data_get($focalPoint, 'y', 50)),
            ];
        }

        return $metadata;
    }

    private function normalizeFocalPointValue(mixed $value): int|float
    {
        if (! is_numeric($value)) {
            return 50;
        }

        $value = max(0, min(100, (float) $value));

        return fmod($value, 1.0) === 0.0 ? (int) $value : $value;
    }
}
