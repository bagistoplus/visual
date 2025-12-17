<?php

namespace BagistoPlus\Visual\Settings;

class Spacing extends Base
{
    protected static string $type = 'spacing';

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
}
