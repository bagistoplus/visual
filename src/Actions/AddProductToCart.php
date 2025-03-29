<?php

namespace BagistoPlus\Visual\Actions;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Shop\Http\Controllers\API\CartController;

class AddProductToCart
{
    protected CartController $cartApi;

    public function __construct(CartController $cartApi)
    {
        $this->cartApi = $cartApi;
    }

    public function execute(array $data)
    {
        request()->request->add($data);

        $response = $this->cartApi->store();

        if ($response instanceof JsonResource) {
            $responseData = $response->resolve();

            return [
                'success' => true,
                'message' => $responseData['message'],
                'redirect_url' => $responseData['redirect'] ?? null,
            ];
        }

        // If it's not a JsonResource, assume it's a JsonResponse (error)
        $responseData = $response->getData(true);

        return [
            'success' => false,
            'message' => $responseData['message'],
            'redirect_url' => $responseData['redirect_uri'],
        ];
    }
}
