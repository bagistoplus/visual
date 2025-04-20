<?php

namespace BagistoPlus\Visual\Actions\Checkout;

use Webkul\Shop\Http\Controllers\API\OnepageController;

class StorePaymentMethod
{
    public function __construct(protected OnepageController $checkoutApi) {}

    public function execute(string $paymentMethod)
    {
        request()->merge(['payment' => ['method' => $paymentMethod]]);

        /** @var \Illuminate\Http\JsonResponse|array */
        $response = $this->checkoutApi->storePaymentMethod();

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response->getData(true);
        }

        return $response;
    }
}
