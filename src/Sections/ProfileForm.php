<?php

namespace BagistoPlus\Visual\Sections;

class ProfileForm extends BladeSection
{
    protected static array $enabledOn = ['account/edit-profile'];

    protected static string $view = 'shop::sections.profile-form';
}
