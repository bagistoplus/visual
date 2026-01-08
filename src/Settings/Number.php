<?php

namespace BagistoPlus\Visual\Settings;

/**
 * @method $this min(int|float $min)
 * @method $this max(int|float $max)
 * @method $this step(int|float $step)
 */
class Number extends Base
{
    protected static string $type = 'number';

    public function min(int|float $min): self
    {
        $this->meta['min'] = $min;

        return $this;
    }

    public function max(int|float $max): self
    {
        $this->meta['max'] = $max;

        return $this;
    }

    public function step(int|float $step): self
    {
        $this->meta['step'] = $step;

        return $this;
    }
}
