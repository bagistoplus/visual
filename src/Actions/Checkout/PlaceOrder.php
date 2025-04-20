<?php

namespace BagistoPlus\Visual\Actions\Checkout;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Shop\Http\Controllers\API\OnepageController;

class PlaceOrder
{
    public function __construct(protected OnepageController $checkoutApi) {}

    public function execute()
    {
        $response = $this->checkoutApi->storeOrder();

        if ($response instanceof JsonResponse) {
            return $response->getData(true);
        } elseif ($response instanceof JsonResource) {
            return $response->resolve();
        }

        return $response;
    }
}
