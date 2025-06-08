<?php

namespace BagistoPlus\Visual\Settings\Support;

class ImageValue
{
    public function __construct(public string $name, public string $path, public string $url) {}

    public function __toString()
    {
        return $this->url;
    }

    public function srcset(): string
    {
        return sprintf(
            '%s 1920w, %s 1280w, %s 1024w, %s 525w',
            $this->url,
            $this->large(),
            $this->medium(),
            $this->small()
        );
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
