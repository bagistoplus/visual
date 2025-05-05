<?php

namespace BagistoPlus\Visual\Sections;

class CustomerReviews extends BladeSection
{
    protected static array $enabledOn = ['account/reviews'];

    protected static string $view = 'shop::sections.customer-reviews';
}
