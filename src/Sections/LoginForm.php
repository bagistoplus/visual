<?php

namespace BagistoPlus\Visual\Sections;

class LoginForm extends BladeSection
{
    protected static array $enabledOn = ['auth/login'];

    protected static string $view = 'shop::sections.login-form';
}
