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

    public function getDefaultWeight(): string
    {
        return in_array('400', $this->weights) ? '400' : ($this->weights[0] ?? '400');
    }

    public function hasWeight(string $weight): bool
    {
        return in_array($weight, $this->weights);
    }

    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'weights' => $this->weights,
            'styles' => $this->styles,
        ];
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
