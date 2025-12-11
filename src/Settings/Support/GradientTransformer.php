<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;

class GradientTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (! $value || ! is_array($value)) {
            $value = $this->getDefault();
        }

        return new GradientValue($value);
    }

    private function getDefault(): array
    {
        return [
            'type' => 'linear',
            'angle' => 90,
            'stops' => [
                ['color' => '#000000ff', 'position' => 0],
                ['color' => '#ffffffff', 'position' => 100],
            ],
        ];
    }
}
