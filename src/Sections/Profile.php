<?php

namespace BagistoPlus\Visual\Sections;

class Profile extends BladeSection
{
    protected static array $enabledOn = ['account/profile'];

    protected static string $view = 'shop::sections.profile';
}
