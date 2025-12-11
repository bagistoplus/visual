<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use matthieumastadenis\couleur\ColorFactory;
use matthieumastadenis\couleur\ColorInterface;
use matthieumastadenis\couleur\colors\HexRgb;

class ColorTransformer implements SettingTransformerInterface
{
    public function transform($color): ColorInterface
    {
        try {
            return ColorFactory::new($color);
        } catch (\Throwable $e) {
            return new HexRgb('00', '00', '00', 'FF');
        }
    }
}
