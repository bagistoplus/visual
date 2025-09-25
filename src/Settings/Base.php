<?php

namespace BagistoPlus\Visual\Settings;

use Craftile\Laravel\Property;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;


abstract class Base extends Property
{
    protected static string $type = 'base';

    public function type(): string
    {
        return static::$type;
    }
}
