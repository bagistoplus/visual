<?php

namespace BagistoPlus\Visual\Settings;

class Checkbox extends Base
{
    protected static string $type = 'checkbox';

    public function asSwitch(): self
    {
        $this->meta['switch'] = true;
        return $this;
    }
}
