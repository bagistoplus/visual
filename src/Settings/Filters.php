<?php

namespace BagistoPlus\Visual\Settings;

/**
 * A list of key and value pairs chosen from a fixed set of keys, which Bagisto's
 * carousels query their api with.
 *
 * @method $this keys(array $keys)
 * @method $this addLabel(string $label)
 */
class Filters extends Base
{
    protected static string $type = 'filters';

    /**
     * The keys a filter may be set on, each with the values it accepts.
     *
     * @param  array<int, array{value: string, label: string, options?: array<int, array{value: string, label: string}>}>  $keys
     */
    public function keys(array $keys): self
    {
        $this->meta['keys'] = array_values($keys);

        return $this;
    }

    /**
     * The label of the button that adds another filter.
     */
    public function addLabel(string $label): self
    {
        $this->meta['addLabel'] = $label;

        return $this;
    }
}
