<?php

namespace BagistoPlus\Visual\Facades;

use BagistoPlus\Visual\Sections\SectionRepository;
use Illuminate\Support\Facades\Facade;

class Sections extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SectionRepository::class;
    }
}
