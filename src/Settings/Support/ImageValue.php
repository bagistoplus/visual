<?php

namespace BagistoPlus\Visual\Settings\Support;

class ImageValue
{
    public function __construct(public string $name, public string $path, public string $url) {}

    public function __toString()
    {
        return $this->url;
    }

    public function small(): string
    {
        return $this->getSizedUrl('small');
    }

    public function medium(): string
    {
        return $this->getSizedUrl('medium');
    }

    public function large(): string
    {
        return $this->getSizedUrl('large');
    }

    private function getSizedUrl(string $size): string
    {
        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->url;
        }

        return url("cache/{$size}/".$this->path);
    }
}
