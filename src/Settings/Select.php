<?php

namespace BagistoPlus\Visual\Settings;

/**
 * @method $this options(array $options)
 */
class Select extends Base
{
    protected static string $type = 'select';

    public function asSegment(): self
    {
        $this->meta['variant'] = 'segment';

        return $this;
    }

    public function options(array $options): self
    {
        $this->meta['options'] = collect($options)->map(function ($item, $key) {
            // If already structured as ['value' => ..., 'label' => ...]
            if (is_array($item) && array_key_exists('value', $item) && array_key_exists('label', $item)) {
                return $item;
            }

            // Otherwise, treat as ['key' => 'label']
            return [
                'value' => $key,
                'label' => $item,
            ];
        })->values()->toArray();

        return $this;
    }
}
