<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use matthieumastadenis\couleur\ColorFactory;
use matthieumastadenis\couleur\ColorInterface;

class ColorTransformer implements SettingTransformerInterface
{
    public function transform($color): ColorInterface
    {
        return ColorFactory::new($color);
    }
}
