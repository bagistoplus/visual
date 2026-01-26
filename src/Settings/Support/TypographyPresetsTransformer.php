<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;

class TypographyPresetsTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, mixed $property = null): mixed
    {
        if (! $value || ! is_array($value)) {
            return collect();
        }

        return collect($value)->map(function ($presetData, $id) {
            return new TypographyValue($presetData, $id);
        });
    }
}
