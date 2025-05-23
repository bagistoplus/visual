<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\View\TailwindPaletteGenerator;
use Illuminate\Support\HtmlString;
use matthieumastadenis\couleur\ColorFactory;
use matthieumastadenis\couleur\ColorSpace;

class ColorSchemeValue
{
    public function __construct(public string $id, public ?array $tokens = [])
    {
        if (empty($this->tokens)) {
            /** @var \BagistoPlus\Visual\Theme\Theme */
            $theme = themes()->current();

            $schemesSetting = collect($theme->settingsSchema)
                ->flatMap(fn ($group) => $group['settings'])
                ->first(fn ($setting) => $setting['type'] === 'color_scheme_group');

            if (! $schemesSetting) {
                return;
            }

            $schemes = $theme->settings->get($schemesSetting['id']);

            $this->tokens = $schemes[$this->id]->tokens;
        }
    }

    public function __toString()
    {
        return $this->id;
    }

    public function attributes(): HtmlString
    {
        return new HtmlString("data-color-scheme=\"{$this->id}\"");
    }

    public function outputCssVars()
    {
        $output = '';

        foreach ($this->tokens as $key => $value) {
            $oklchColor = ColorFactory::new($value, ColorSpace::OkLch);
            $output .= "  --color-$key: {$oklchColor->stringify()};\n";

            if (! str_starts_with($key, 'on-')) {
                $shades = $this->generateOklchShades($key, $value);
                foreach ($shades as $shade) {
                    $output .= $shade."\n";
                }
            }
        }

        return $output;
    }

    private function generateOklchShades(string $name, string $color)
    {
        return collect(TailwindPaletteGenerator::generate(ColorFactory::newRgb($color)))
            ->map(function ($color) {
                return ColorFactory::newOkLch($color->__toString(), ColorSpace::Rgb)
                    ->stringify();
            })->map(function ($color, $shade) use ($name) {
                return "    --color-{$name}-{$shade}: {$color};";
            });
    }
}
