<?php

namespace BagistoPlus\Visual\Settings\Support;

class Image
{
    public function __construct(public string $name, public string $path, public string $url) {}

    public function __toString()
    {
        return $this->url;
    }
}
