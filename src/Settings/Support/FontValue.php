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
        return self::linksFor([$this]);
    }

    /**
     * This font in Bunny Fonts' family syntax: the slug, then each weight with an "i"
     * suffix for italic, as in "inter:400,700i".
     */
    public function familyQuery(): string
    {
        $query = [];

        foreach ($this->weights as $weight) {
            foreach ($this->styles as $style) {
                $query[] = $weight.($style === 'italic' ? 'i' : '');
            }
        }

        return $query === [] ? $this->slug : $this->slug.':'.implode(',', $query);
    }

    /**
     * The links that load the given fonts from Bunny Fonts in a single stylesheet request.
     *
     * The stylesheet is fetched as a preload and applied once it arrives, so the page renders
     * without waiting on a connection to another host; display=swap shows text in the fallback
     * font until the fonts load. Without JavaScript the noscript link loads it the usual way.
     *
     * @param  array<int, self>  $fonts
     */
    public static function linksFor(array $fonts): HtmlString
    {
        if ($fonts === []) {
            return new HtmlString('');
        }

        $families = implode('|', array_map(fn (self $font) => $font->familyQuery(), $fonts));
        $href = e('https://fonts.bunny.net/css?family='.$families.'&display=swap');

        return new HtmlString(
            '<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>'
            ."\n".'  <link href="'.$href.'" rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" />'
            ."\n".'  <noscript><link href="'.$href.'" rel="stylesheet" /></noscript>'
        );
    }
}
