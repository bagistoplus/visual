<?php

namespace BagistoPlus\Visual\Settings;

use Craftile\Laravel\Property;

abstract class Base extends Property
{
    protected static string $type = 'base';

    public function type(): string
    {
        return static::$type;
    }
}
