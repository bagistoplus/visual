<?php

namespace BagistoPlus\Visual\Settings;

/**
 * @method $this min(int|float $min)
 * @method $this max(int|float $max)
 * @method $this step(int|float $step)
 * @method $this unit(string $unit)
 */
class Range extends Base
{
    public static string $component = 'range-setting';

    public int|float $min = 1;

    public int|float $max = 100;

    public int|float $step = 1;

    public string $unit = '';

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
            'unit' => $this->unit,
        ]);
    }
}
