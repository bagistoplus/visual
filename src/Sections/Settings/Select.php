<?php

namespace BagistoPlus\Visual\Sections\Settings;

/**
 * @method $this options(array $options)
 */
class Select extends Base
{
    public static string $component = 'select-setting';

    public array $options = [];

    public function options(array $options): self
    {
        $this->options = collect($options)->map(function ($item, $key) {
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

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->options,
        ]);
    }
}
