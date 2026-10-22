<?php

namespace BagistoPlus\Visual\Support;

use GdImage;

/**
 * Lays a desktop and a mobile screenshot of a theme into one preview: the
 * desktop view filling the image, the mobile view in a phone over it.
 *
 * Everything is drawn at twice the output size and scaled down at the end,
 * since GD draws curves without anti-aliasing.
 */
class ThemePreviewComposer
{
    protected const WIDTH = 1280;

    protected const HEIGHT = 720;

    protected const SCALE = 2;

    public function compose(string $desktopPath, string $mobilePath, string $outputPath): void
    {
        $canvas = imagecreatetruecolor(self::WIDTH * self::SCALE, self::HEIGHT * self::SCALE);
        imagealphablending($canvas, true);

        $this->paste($canvas, $this->load($desktopPath), 0, 0, self::WIDTH, self::HEIGHT, 0);
        $this->drawPhone($canvas, $this->load($mobilePath));

        $output = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagecopyresampled($output, $canvas, 0, 0, 0, 0, self::WIDTH, self::HEIGHT, imagesx($canvas), imagesy($canvas));
        imagepng($output, $outputPath);
    }

    /**
     * The mobile view in a phone, in the lower right corner, running off the bottom edge.
     */
    protected function drawPhone(GdImage $canvas, GdImage $screenshot): void
    {
        $size = 1.6;
        $w = 222 * $size;
        $h = 466 * $size;
        $body = ['x' => self::WIDTH - 48 - $w, 'y' => self::HEIGHT + 250 - $h, 'w' => $w, 'h' => $h];
        $bezel = 9 * $size;
        $radius = 36 * $size;

        $this->shadow($canvas, $body['x'], $body['y'], $body['w'], $body['h']);

        $this->roundedRectangle($canvas, $body['x'] - 3 * $size, $body['y'] + 92 * $size, 4 * $size, 34 * $size, 2 * $size, [40, 40, 44]);
        $this->roundedRectangle($canvas, $body['x'] - 3 * $size, $body['y'] + 136 * $size, 4 * $size, 34 * $size, 2 * $size, [40, 40, 44]);
        $this->roundedRectangle($canvas, $body['x'] + $body['w'] - $size, $body['y'] + 112 * $size, 4 * $size, 54 * $size, 2 * $size, [40, 40, 44]);

        $this->roundedRectangle($canvas, $body['x'] - 2, $body['y'] - 2, $body['w'] + 4, $body['h'] + 4, $radius + 2, [86, 88, 96]);
        $this->roundedRectangle($canvas, $body['x'], $body['y'], $body['w'], $body['h'], $radius, [10, 10, 12]);

        $screen = [$body['x'] + $bezel, $body['y'] + $bezel, $body['w'] - $bezel * 2, $body['h'] - $bezel * 2];
        $statusBar = 30 * $size;

        $this->roundedRectangle($canvas, ...$screen, radius: $radius - $bezel, color: $this->pixel($screenshot, 0, 0));
        $this->paste($canvas, $screenshot, $screen[0], $screen[1] + $statusBar, $screen[2], $screen[3] - $statusBar, $radius - $bezel);

        $this->roundedRectangle($canvas, $body['x'] + $body['w'] / 2 - 32 * $size, $body['y'] + $bezel + 8 * $size, 64 * $size, 18 * $size, 9 * $size, [10, 10, 12]);
    }

    /**
     * A screenshot scaled into a box, its top kept and its corners rounded.
     */
    protected function paste(GdImage $canvas, GdImage $screenshot, float $x, float $y, float $w, float $h, float $radius): void
    {
        [$x, $y, $w, $h, $radius] = array_map(fn ($value) => (int) round($value * self::SCALE), [$x, $y, $w, $h, $radius]);

        $sourceWidth = imagesx($screenshot);
        $sourceHeight = min(imagesy($screenshot), (int) round($sourceWidth * $h / $w));

        $screen = imagecreatetruecolor($w, $h);
        imagealphablending($screen, false);
        imagesavealpha($screen, true);
        imagecopyresampled($screen, $screenshot, 0, 0, 0, 0, $w, $h, $sourceWidth, $sourceHeight);

        $transparent = imagecolorallocatealpha($screen, 0, 0, 0, 127);

        for ($py = 0; $py < $radius; $py++) {
            for ($px = 0; $px < $radius; $px++) {
                if (($radius - $px - 0.5) ** 2 + ($radius - $py - 0.5) ** 2 <= $radius ** 2) {
                    continue;
                }

                imagesetpixel($screen, $px, $py, $transparent);
                imagesetpixel($screen, $w - 1 - $px, $py, $transparent);
                imagesetpixel($screen, $px, $h - 1 - $py, $transparent);
                imagesetpixel($screen, $w - 1 - $px, $h - 1 - $py, $transparent);
            }
        }

        imagecopy($canvas, $screen, $x, $y, 0, 0, $w, $h);
    }

    /**
     * A soft shadow under a rounded shape, blurred on a small copy and scaled
     * back up, which is far cheaper than blurring at full size.
     */
    protected function shadow(GdImage $canvas, float $x, float $y, float $w, float $h): void
    {
        $factor = 8;
        $width = (int) (imagesx($canvas) / $factor);
        $height = (int) (imagesy($canvas) / $factor);
        $scale = self::SCALE / $factor;

        $mask = imagecreatetruecolor($width, $height);
        imagefilledrectangle($mask, 0, 0, $width, $height, $this->allocate($mask, [255, 255, 255]));
        imagefilledrectangle(
            $mask,
            (int) round(($x + 8) * $scale),
            (int) round(($y + 24) * $scale),
            (int) round(($x + $w - 8) * $scale),
            (int) round(($y + $h) * $scale),
            $this->allocate($mask, [0, 0, 0])
        );

        // GD blurs colour but keeps each pixel's alpha, so the shape is blurred
        // as grey on white and only then turned into transparency.
        for ($pass = 0; $pass < 16; $pass++) {
            imagefilter($mask, IMG_FILTER_GAUSSIAN_BLUR);
        }

        $layer = imagecreatetruecolor($width, $height);
        imagealphablending($layer, false);
        imagesavealpha($layer, true);

        for ($py = 0; $py < $height; $py++) {
            for ($px = 0; $px < $width; $px++) {
                $darkness = 1 - (imagecolorat($mask, $px, $py) & 0xFF) / 255;
                imagesetpixel($layer, $px, $py, imagecolorallocatealpha($layer, 0, 0, 0, (int) round(127 - $darkness * 70)));
            }
        }

        imagecopyresampled($canvas, $layer, 0, 0, 0, 0, imagesx($canvas), imagesy($canvas), $width, $height);
    }

    /**
     * @param  array{int, int, int}  $color
     */
    protected function roundedRectangle(GdImage $canvas, float $x, float $y, float $w, float $h, float $radius, array $color): void
    {
        [$x, $y, $w, $h, $radius] = array_map(fn ($value) => (int) round($value * self::SCALE), [$x, $y, $w, $h, $radius]);
        $fill = $this->allocate($canvas, $color);
        $radius = min($radius, intdiv($w, 2), intdiv($h, 2));

        imagefilledrectangle($canvas, $x + $radius, $y, $x + $w - $radius - 1, $y + $h - 1, $fill);
        imagefilledrectangle($canvas, $x, $y + $radius, $x + $w - 1, $y + $h - $radius - 1, $fill);

        foreach ([[$x + $radius, $y + $radius], [$x + $w - $radius - 1, $y + $radius], [$x + $radius, $y + $h - $radius - 1], [$x + $w - $radius - 1, $y + $h - $radius - 1]] as [$cx, $cy]) {
            imagefilledellipse($canvas, $cx, $cy, $radius * 2, $radius * 2, $fill);
        }
    }

    /**
     * @return array{int, int, int}
     */
    protected function pixel(GdImage $image, int $x, int $y): array
    {
        $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));

        return [$color['red'], $color['green'], $color['blue']];
    }

    protected function load(string $path): GdImage
    {
        return imagecreatefromstring(file_get_contents($path));
    }

    /**
     * @param  array{int, int, int}  $color
     */
    protected function allocate(GdImage $image, array $color): int
    {
        return imagecolorallocate($image, ...$color);
    }
}
