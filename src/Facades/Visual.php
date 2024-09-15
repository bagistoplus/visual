<?php

namespace BagistoPlus\Visual\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BagistoPlus\Visual\Visual
 */
class Visual extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BagistoPlus\Visual\Visual::class;
    }
}
