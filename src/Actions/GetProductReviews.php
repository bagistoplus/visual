<?php

namespace BagistoPlus\Visual\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\Shop\Http\Controllers\API\ReviewController;

class GetProductReviews
{
    public function __construct(protected ReviewController $reviewApi) {}

    /**
     * Get product reviews
     *
     * @return LengthAwarePaginator
     */
    public function execute(int $productId, array $params = [])
    {
        request()->query->add($params);

        $response = $this->reviewApi->index($productId);

        return $response->resource;
    }
}
