<?php

namespace BagistoPlus\Visual\Actions;

use Webkul\Shop\Http\Controllers\API\WishlistController;

class MoveWishlistToCart
{
    public function __construct(protected WishlistController $wishlistApi) {}

    public function execute($id, $productId, $quantity = 1)
    {
        request()->merge([
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);

        $response = $this->wishlistApi->moveToCart($id);

        return $response->resolve();
    }
}
