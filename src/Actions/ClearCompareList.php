<?php

namespace BagistoPlus\Visual\Actions;

use Webkul\Shop\Http\Controllers\API\CompareController;

class ClearCompareList
{
    public function __construct(protected CompareController $compareApi) {}

    /**
     * Remove all items from compare list
     */
    public function execute()
    {
        $response = $this->compareApi->destroyAll();
        $responseData = $response->toArray(request());

        session()->flash('success', $responseData['message']);
    }
}
