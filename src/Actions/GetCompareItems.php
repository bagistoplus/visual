<?php

namespace BagistoPlus\Visual\Actions;

use Webkul\Shop\Http\Controllers\API\CompareController;

class GetCompareItems
{
    public function __construct(protected CompareController $compareApi) {}

    /**
     * Get compare items
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function execute(array $productIds)
    {
        request()->request->add(['product_ids' => $productIds]);

        $response = $this->compareApi->index();

        return $response->resource;
    }
}
