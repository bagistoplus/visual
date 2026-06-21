<?php

namespace BagistoPlus\Visual\Settings;

use Craftile\Laravel\Property;

abstract class Base extends Property
{
    protected static string $type = 'base';

    protected bool $localized = false;

    public function type(): string
    {
        return static::$type;
    }

    public function localized(bool $localized = true): static
    {
        $this->localized = $localized;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'localized' => $this->localized,
        ]);
    }
}
