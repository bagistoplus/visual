<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;

class SpacingTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (! $value || ! is_array($value)) {
            $value = $this->getDefault();
        }

        return new SpacingValue($value);
    }

    private function getDefault(): array
    {
        return [
            'top' => 0,
            'right' => 0,
            'bottom' => 0,
            'left' => 0,
        ];
    }
}
