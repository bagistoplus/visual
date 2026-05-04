<?php

namespace BagistoPlus\Visual\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\Shop\Http\Controllers\API\ProductController;

class GetProducts
{
    public function __construct(protected ProductController $productApi) {}

    /**
     * Get products
     *
     * @return LengthAwarePaginator
     */
    public function execute(array $params)
    {
        request()->query->add($params);

        $response = $this->productApi->index();

        return $response->resource;
    }
}
