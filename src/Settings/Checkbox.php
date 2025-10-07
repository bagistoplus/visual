<?php

namespace BagistoPlus\Visual\Settings;

class Checkbox extends Base
{
    protected static string $type = 'boolean';

    public function asSwitch(): self
    {
        $this->meta['switch'] = true;

        return $this;
    }
}
