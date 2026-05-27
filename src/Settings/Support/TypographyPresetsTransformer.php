<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;

class TypographyPresetsTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, array $schema = []): mixed
    {
        if (! $value || ! is_array($value)) {
            return new TypographyPresetCollection;
        }

        return (new TypographyPresetCollection($value))->map(function ($presetData, $id) {
            return new TypographyValue($presetData, $id);
        });
    }
}
