<?php

namespace BagistoPlus\Visual\Enums;

interface Events
{
    public const CART_UPDATED = 'cart_updated';

    public const SHIPPING_METHOD_SET = 'shipping_method_set';

    public const COUPON_APPLIED = 'coupon_applied';

    public const COUPON_REMOVED = 'coupon_removed';

    public const PAYMENT_METHOD_SET = 'checkout:payment_method_set';
}
