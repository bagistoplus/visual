<?php

namespace BagistoPlus\Visual\Settings;

class ColorToken extends Base
{
    protected static string $type = 'color_token';

    public function allowNone(string $label = 'None'): static
    {
        $this->meta['allowNone'] = $label;

        return $this;
    }
}
