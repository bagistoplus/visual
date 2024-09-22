<?php

namespace BagistoPlus\Visual\Sections;

class AnnouncementBar extends BladeSection
{
    protected static string $schema = __DIR__.'/../../resources/schemas/announcement-bar.json';

    protected static string $view = 'shop::sections.announcement-bar';
}
