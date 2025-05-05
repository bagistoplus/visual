<?php

namespace BagistoPlus\Visual\Sections\Concerns;

use matthieumastadenis\couleur\ColorFactory;

class ColorTransformer
{
    public function __invoke(string $color)
    {
        return ColorFactory::new($color);
    }
}
