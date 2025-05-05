<?php

namespace BagistoPlus\Visual\Settings\Support;

class LinkTransformer
{
    public function __invoke(?string $url = null)
    {
        if (! $url) {
            return null;
        }

        if (strpos($url, 'visual://') !== 0) {
            return $url;
        }

        if (preg_match('/^visual:\/\/([^:]+):([^\/]+)\/(.*)?$/', $url, $matches)) {
            return match ($matches[1]) {
                'categories' => url($matches[3]),
                'products' => url($matches[3]),
                'cms_pages' => route('shop.cms.page', ['slug' => $matches[3]]),
                default => url($matches[3]),
            };
        }

        return $url;
    }
}
