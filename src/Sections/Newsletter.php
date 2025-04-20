<?php

namespace BagistoPlus\Visual\Sections;

class Newsletter extends BladeSection
{
    protected static string $view = 'shop::sections.newsletter';

    protected static string $schema = __DIR__.'/../../resources/schemas/newsletter.json';
}
