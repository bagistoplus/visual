<?php

namespace BagistoPlus\Visual\Actions;

use Webkul\Shop\Http\Controllers\API\WishlistController;

class ClearWishlist
{
    public function __construct(protected WishlistController $wishlistApi) {}

    public function execute()
    {
        $response = $this->wishlistApi->destroyAll();

        return $response->resolve();
    }
}
