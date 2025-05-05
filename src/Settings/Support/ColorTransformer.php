<?php

namespace BagistoPlus\Visual\Settings\Support;

use matthieumastadenis\couleur\ColorFactory;

class ColorTransformer
{
    public function __invoke(string $color)
    {
        return ColorFactory::new($color);
    }
}
