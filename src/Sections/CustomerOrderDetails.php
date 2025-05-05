<?php

namespace BagistoPlus\Visual\Sections;

class CustomerOrderDetails extends BladeSection
{
    protected static array $enabledOn = ['account/order-details'];

    protected static string $view = 'shop::sections.customer-order-details';
}
