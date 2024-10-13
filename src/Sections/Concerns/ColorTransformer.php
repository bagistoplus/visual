<?php

namespace BagistoPlus\Visual\Sections\Concerns;

use Spatie\Color\Factory;

class ColorTransformer
{
    public function __invoke(string $color)
    {
        return Factory::fromString($color);
    }
}
