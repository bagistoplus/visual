<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;

class RadiusTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, array $schema = []): RadiusValue
    {
        if (RadiusValue::has($value)) {
            return new RadiusValue($value);
        }

        $default = $schema['default'] ?? null;

        if (RadiusValue::has($default)) {
            return new RadiusValue($default);
        }

        return new RadiusValue('md');
    }
}
