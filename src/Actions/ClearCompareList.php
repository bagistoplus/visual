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
        /** @var \Illuminate\Http\Resources\Json\JsonResource */
        $response = $this->compareApi->destroyAll();

        return $response->resolve();
    }
}
