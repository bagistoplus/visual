<?php

namespace BagistoPlus\Visual\Actions\Cart;

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

        /** @var \Illuminate\Http\Resources\Json\JsonResource */
        $response = $this->wishlistApi->store();

        return $response->resolve();
    }
}
