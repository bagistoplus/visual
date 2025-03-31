<?php

namespace BagistoPlus\Visual\Actions\Checkout;

use Webkul\Shop\Http\Controllers\API\OnepageController;

class StoreShippingMethod
{
    public function __construct(protected OnepageController $checkoutApi) {}

    public function execute(string $shippingMethod)
    {
        request()->merge(['shipping_method' => $shippingMethod]);

        /** @var \Illuminate\Http\JsonResponse */
        $response = $this->checkoutApi->storeShippingMethod();

        return $response->getData(true);
    }
}
