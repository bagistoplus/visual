<?php

namespace BagistoPlus\Visual\Sections;

class Hero extends BladeSection
{
    protected static string $view = 'shop::sections.hero';

    protected static string $schema = __DIR__.'/../../resources/schemas/hero.json';
}
