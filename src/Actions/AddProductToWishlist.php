<?php

namespace BagistoPlus\Visual\Actions;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Shop\Http\Controllers\API\WishlistController;

class AddProductToWishlist
{
    protected WishlistController $wishlistApi;

    public function __construct(WishlistController $wishlistApi)
    {
        $this->wishlistApi = $wishlistApi;
    }

    public function execute($productId)
    {
        // We replace request body with $data to discard custom livewire inputs
        $originalRequestData = request()->request->all();
        request()->request->replace([
            'product_id' => $productId,
        ]);

        $response = $this->wishlistApi->store();

        // and then we restore livewire request data to prevent ignition crash
        // we should probably move this logic to some livewire middleware
        request()->request->replace($originalRequestData);

        if ($response instanceof JsonResource) {
            $responseData = $response->toArray(request());

            session()->flash('info', $responseData['message']);
        }
    }
}
