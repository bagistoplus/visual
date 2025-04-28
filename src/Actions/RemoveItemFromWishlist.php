<?php

namespace BagistoPlus\Visual\Actions;

use Webkul\Shop\Http\Controllers\API\WishlistController;

class RemoveItemFromWishlist
{
    public function __construct(protected WishlistController $wishlistApi) {}

    public function execute($id)
    {
        $response = $this->wishlistApi->destroy($id);

        return $response->resolve();
    }
}
