<?php

namespace BagistoPlus\Visual\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\Shop\Http\Controllers\API\CompareController;

class GetCompareItems
{
    public function __construct(protected CompareController $compareApi) {}

    /**
     * Get compare items
     *
     * @return LengthAwarePaginator
     */
    public function execute(array $productIds)
    {
        request()->request->add(['product_ids' => $productIds]);

        $response = $this->compareApi->index();

        return $response->resource;
    }
}
