<?php

namespace BagistoPlus\Visual\Sections;

class CustomerEditAddress extends BladeSection
{
    protected static array $enabledOn = ['account/edit-address'];

    protected static string $view = 'shop::sections.customer-edit-address';
}
