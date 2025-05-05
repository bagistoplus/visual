<?php

namespace BagistoPlus\Visual\Sections;

class CustomerOrders extends BladeSection
{
    protected static array $enabledOn = ['account/orders'];

    protected static string $view = 'shop::sections.customer-orders';
}
