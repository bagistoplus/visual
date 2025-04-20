<?php

namespace BagistoPlus\Visual\Actions;

use Webkul\Shop\Http\Controllers\API\WishlistController;

class GetWishlistItems
{
    public function __construct(protected WishlistController $wishlistApi) {}

    public function execute()
    {
        $response = $this->wishlistApi->index();

        return collect($response->response()->getData()->data);
    }
}
