<?php

namespace BagistoPlus\Visual\Sections;

class RegisterForm extends BladeSection
{
    protected static array $enabledOn = ['auth/register'];

    protected static string $view = 'shop::sections.register-form';
}
