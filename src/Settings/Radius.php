<?php

namespace BagistoPlus\Visual\Settings;

use BagistoPlus\Visual\Settings\Support\RadiusValue;

class Radius extends Base
{
    protected static string $type = 'radius';

    public static function make(string $id, string $label): static
    {
        return parent::make($id, $label)->default('md');
    }

    /**
     * Restrict the keys offered in the editor. Unknown keys are ignored.
     *
     * @param  array<int, string>  $options
     */
    public function options(array $options): static
    {
        $this->meta['options'] = array_values(array_filter($options, fn ($key) => RadiusValue::has($key)));

        return $this;
    }
}
