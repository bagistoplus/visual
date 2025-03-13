<?php

namespace BagistoPlus\Visual\Actions;

use Webkul\Shop\Http\Controllers\API\CompareController;

class RemoveItemFromCompareList
{
    public function __construct(protected CompareController $compareApi) {}

    /**
     * Remove item from compare list
     */
    public function execute($productId)
    {
        request()->request->add(['product_id' => $productId]);

        $response = $this->compareApi->destroy();
        $responseData = $response->toArray(request());

        session()->flash('success', $responseData['message']);
    }
}
