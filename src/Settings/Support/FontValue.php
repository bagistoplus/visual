<?php

namespace BagistoPlus\Visual\Settings\Support;

use Illuminate\Support\HtmlString;

class FontValue
{
    public function __construct(public string $slug, public string $name, public array $weights, public array $styles) {}

    public function __toString()
    {
        return $this->name;
    }

    public function toHtml()
    {
        $base = '<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>';
        $query = [];

        foreach ($this->weights as $weight) {
            foreach ($this->styles as $style) {
                $query[] = $weight.($style === 'italic' ? 'i' : '');
            }
        }

        $base .= "\n".'  <link href="https://fonts.bunny.net/css?family='.$this->slug.':'.implode(',', $query).'" rel="preload" as="style" />';
        $base .= "\n".'  <link href="https://fonts.bunny.net/css?family='.$this->slug.':'.implode(',', $query).'" rel="stylesheet" />';

        return new HtmlString($base);
    }
}
