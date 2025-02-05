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
        // We replace request body with $data to discard custom livewire inputs
        $originalRequestData = request()->request->all();
        request()->request->replace($data);

        $response = $this->cartApi->store();

        // and then we restore livewire request data to prevent ignition crash
        // we should probably move this logic to some livewire middleware
        request()->request->replace($originalRequestData);

        if ($response instanceof JsonResource) {
            $responseData = $response->toArray(request());

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
