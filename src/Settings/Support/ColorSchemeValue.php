<?php

namespace BagistoPlus\Visual\Settings\Support;

use Illuminate\Support\HtmlString;
use matthieumastadenis\couleur\ColorFactory;
use matthieumastadenis\couleur\ColorSpace;

class ColorSchemeValue
{
    public function __construct(public string $id, public array $tokens = []) {}

    public function __toString()
    {
        return $this->id;
    }

    public function attributes(): HtmlString
    {
        return new HtmlString("data-color-scheme=\"{$this->id}\"");
    }

    /**
     * One CSS variable per scheme token, as `--color-{token}`. Lighter or darker
     * variants are left to the theme: an opacity modifier (bg-primary/10) or
     * color-mix() over these tokens follows every scheme, dark ones included.
     */
    public function outputCssVars()
    {
        $output = '';

        foreach ($this->tokens as $key => $value) {
            $oklchColor = ColorFactory::new($value, ColorSpace::OkLch);
            $output .= "  --color-$key: {$oklchColor->stringify()};\n";
        }

        return $output;
    }
}
