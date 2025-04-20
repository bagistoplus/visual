<?php

namespace BagistoPlus\Visual\View;

use Spatie\Color\Color;
use Spatie\Color\Factory;
use Spatie\Color\Rgb;

class TailwindPaletteGenerator
{
    public static Color $white;

    public static Color $black;

    public static $lightWeights = [
        50 => 0.95,
        100 => 0.9,
        200 => 0.75,
        300 => 0.6,
        400 => 0.3,
    ];

    public static $darkWeights = [
        600 => 0.1,
        700 => 0.4,
        800 => 0.55,
        900 => 0.7,
        950 => 0.75,
    ];

    public static function white()
    {
        if (! isset(self::$white)) {
            self::$white = Rgb::fromString('rgb(255,255,255)');
        }

        return self::$white;
    }

    public static function black()
    {
        if (! isset(self::$black)) {
            self::$black = Rgb::fromString('rgb(0,0,0)');
        }

        return self::$black;
    }

    public static function generate(Color|string $color, bool $isDarkScheme = false)
    {
        if (is_string($color)) {
            $color = Factory::fromString($color);
        }

        $rgbColor = $color->toRgb();

        $colors = [];

        foreach (self::$lightWeights as $key => $weight) {
            $colors[$key] = self::lighten($rgbColor, $weight);
        }

        $colors[500] = $rgbColor;

        foreach (self::$darkWeights as $key => $weight) {
            $colors[$key] = self::darken($rgbColor, $weight);
        }

        if ($isDarkScheme) {
            $colors = array_combine(
                array_keys($colors),
                array_reverse(array_values($colors))
            );
        }

        return $colors;
    }

    public static function mixColor(Color $color1, Color $color2, $weight = 0.5)
    {
        $f = function ($x) use ($weight) {
            return (1 - $weight) * $x;
        };

        $g = function ($x) use ($weight) {
            return $weight * $x;
        };

        $h = function ($x, $y) {
            return intval(round($x + $y));
        };

        $channels = array_map(
            $h,
            array_map($f, [$color1->red(), $color1->green(), $color1->blue()]),
            array_map($g, [$color2->red(), $color2->green(), $color2->blue()]),
        );

        return new Rgb($channels[0], $channels[1], $channels[2]);
    }

    public static function lighten(Color $color, $weight = 0.5)
    {
        return self::mixColor($color, self::white(), $weight);
    }

    public static function darken(Color $color, $weight = 0.5)
    {
        return self::mixColor($color, self::black(), $weight);
    }
}
