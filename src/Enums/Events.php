<?php

namespace BagistoPlus\Visual\Enums;

interface Events
{
    public const CART_UPDATED = 'visual:cart_updated';

    public const SHIPPING_METHOD_SET = 'visual:shipping_method_set';

    public const COUPON_APPLIED = 'visual:coupon_applied';

    public const COUPON_REMOVED = 'visual:coupon_removed';

    public const PAYMENT_METHOD_SET = 'visual:payment_method_set';
}
