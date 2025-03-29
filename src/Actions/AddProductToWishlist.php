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
        request()->request->add([
            'product_id' => $productId,
        ]);

        $response = $this->wishlistApi->store();

        if ($response instanceof JsonResource) {
            $responseData = $response->resolve();

            session()->flash('info', $responseData['message']);
        }
    }
}
