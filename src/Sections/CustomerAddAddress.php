<?php

namespace BagistoPlus\Visual\Sections;

class CustomerAddAddress extends BladeSection
{
    protected static array $enabledOn = ['account/add-address'];

    protected static string $view = 'shop::sections.customer-add-address';
}
