<?php

namespace BagistoPlus\Visual\Actions\Cart;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Shop\Http\Controllers\API\CartController;

class StoreCoupon
{
    public function __construct(protected CartController $cartApi) {}

    public function execute(string $couponCode)
    {
        request()->merge(['code' => $couponCode]);

        $response = $this->cartApi->storeCoupon();

        if ($response instanceof JsonResponse) {
            return [
                ...$response->getData(true),
                'success' => false,
            ];
        } elseif ($response instanceof JsonResource) {
            return [
                ...$response->resolve(),
                'success' => true,
            ];
        }

        return $response;
    }
}
