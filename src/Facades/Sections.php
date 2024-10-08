<?php

namespace BagistoPlus\Visual\Facades;

use BagistoPlus\Visual\Sections\SectionRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed all()
 * @method static mixed add(\BagistoPlus\Visual\Sections\Section $section)
 * @method static mixed has(string $slug)
 * @method static mixed get(string $slug)
 *
 * @see \BagistoPlus\Visual\Sections\SectionRepository
 */
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
