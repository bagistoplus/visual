<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;

class ColorTokenTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, array $schema = []): ?ColorTokenValue
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        if ($value === ColorTokenValue::EMPTY_VALUE) {
            return ColorTokenValue::empty();
        }

        if (! in_array($value, ColorTokenValue::TOKENS, true)) {
            return null;
        }

        return new ColorTokenValue($value);
    }
}
