<?php

namespace BagistoPlus\Visual\Sections;

class ErrorPage extends BladeSection
{
    protected static array $enabledOn = ['error'];

    protected static string $view = 'shop::sections.error-page';
}
