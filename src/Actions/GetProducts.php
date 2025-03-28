<?php

namespace BagistoPlus\Visual\Actions;

use Webkul\Shop\Http\Controllers\API\ProductController;

class GetProducts
{
    public function __construct(protected ProductController $productApi) {}

    /**
     * Get products
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function execute(array $params)
    {
        request()->query->add($params);

        $response = $this->productApi->index();
        // $paginator = $response->resource;
        // $paginator->setCollection(collect($response->response()->getData()->data));

        // return $paginator;

        return $response->resource;
    }
}
