<?php

namespace BagistoPlus\Visual\Support\Images;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager as InterventionImageManager;

/**
 * Scales an image down to WebP copies with whatever the installed Bagisto
 * provides: Intervention Image 4 (Bagisto 2.5 and later), 3 or 2 before
 * that. Without any of them, or without WebP support, nothing is resized.
 */
class ImageResizer
{
    public function available(): bool
    {
        return $this->strategy() !== null;
    }

    /**
     * The image's dimensions, upright as its EXIF orientation says, and a WebP
     * copy at each of the given widths that is smaller than the image.
     *
     * @param  list<int>  $widths
     * @return array{width: int, height: int, variants: array<int, string>}
     */
    public function resize(string $contents, array $widths, int $quality): array
    {
        rsort($widths);

        return match ($this->strategy()) {
            'intervention-4' => $this->resizeWithIntervention('decode', $contents, $widths, $quality),
            'intervention-3' => $this->resizeWithIntervention('read', $contents, $widths, $quality),
            'intervention-2' => $this->resizeWithIntervention2($contents, $widths, $quality),
            default => throw new \RuntimeException('No image library that can write WebP is installed.'),
        };
    }

    protected function strategy(): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        if ($this->interventionHas('decode')) {
            return 'intervention-4';
        }

        if ($this->interventionHas('read')) {
            return 'intervention-3';
        }

        if ($this->interventionHas('make')) {
            return 'intervention-2';
        }

        return null;
    }

    /**
     * Which Intervention major is installed, told apart by a method only it has.
     */
    protected function interventionHas(string $method): bool
    {
        return class_exists(InterventionImageManager::class) && method_exists(InterventionImageManager::class, $method);
    }

    /**
     * Intervention 3 and 4 orient on decode and scale the same image down from
     * the largest width, which keeps each step a downscale. Only the decode
     * method's name differs between them.
     *
     * @param  'decode'|'read'  $decode
     * @param  list<int>  $widths
     * @return array{width: int, height: int, variants: array<int, string>}
     */
    protected function resizeWithIntervention(string $decode, string $contents, array $widths, int $quality): array
    {
        $image = $this->interventionManager(Driver::class)->{$decode}($contents);
        $width = $image->width();
        $height = $image->height();

        $variants = [];

        foreach ($this->below($widths, $width) as $variantWidth) {
            $image->scaleDown(width: $variantWidth);
            $variants[$variantWidth] = $image->encode(new WebpEncoder(quality: $quality))->toString();
        }

        return ['width' => $width, 'height' => $height, 'variants' => $variants];
    }

    /**
     * @param  list<int>  $widths
     * @return array{width: int, height: int, variants: array<int, string>}
     */
    protected function resizeWithIntervention2(string $contents, array $widths, int $quality): array
    {
        $image = $this->interventionManager(['driver' => 'gd'])->make($contents);

        // Needs the exif extension; without it the image keeps its stored orientation.
        rescue(fn () => $image->orientate(), report: false);

        $width = $image->width();
        $height = $image->height();

        $variants = [];

        foreach ($this->below($widths, $width) as $variantWidth) {
            $image->widen($variantWidth, fn ($constraint) => $constraint->upsize());
            $variants[$variantWidth] = (string) $image->encode('webp', $quality);
        }

        return ['width' => $width, 'height' => $height, 'variants' => $variants];
    }

    /**
     * @param  list<int>  $widths
     * @return list<int>
     */
    protected function below(array $widths, int $width): array
    {
        return array_values(array_filter($widths, fn (int $candidate) => $candidate < $width));
    }

    /**
     * Intervention's manager for the installed major. Typed loosely on purpose:
     * its API differs between majors, and only the matching one runs.
     */
    protected function interventionManager(mixed $driver): mixed
    {
        return new InterventionImageManager($driver);
    }
}
