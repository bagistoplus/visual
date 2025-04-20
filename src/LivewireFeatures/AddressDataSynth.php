<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use BagistoPlus\Visual\Data\AddressData;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class AddressDataSynth extends Synth
{
    public static $key = 'address-data';

    public static function match($target): bool
    {
        return $target instanceof AddressData;
    }

    public function dehydrate($target): array
    {
        return [$target->toArray(), []];
    }

    public function hydrate($value, $meta): mixed
    {
        return AddressData::fromArray($value);
    }

    public function get(&$target, $key)
    {
        return $target->{$key} ?? null;
    }

    public function set(&$target, $key, $value): void
    {
        if (property_exists($target, $key)) {
            $target->$key = $value;
        }
    }

    public function unset(&$target, $key): void
    {
        if (! property_exists($target, $key)) {
            return;
        }

        $value = $target->$key;

        $target->$key = match (true) {
            is_array($value) => [],
            is_bool($value) => false,
            is_int($value), is_float($value) => null,
            default => '',
        };
    }
}
