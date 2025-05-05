<?php

namespace BagistoPlus\Visual\Sections;

class CheckoutSuccess extends BladeSection
{
    protected static array $enabledOn = ['checkout-success'];

    protected static string $view = 'shop::sections.checkout-success';
}
