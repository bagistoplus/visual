<?php

namespace BagistoPlus\Visual\Sections;

class CustomerAddresses extends BladeSection
{
    protected static array $enabledOn = ['account/addresses'];

    protected static string $view = 'shop::sections.customer-addresses';
}
