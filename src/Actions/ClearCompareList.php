<?php

namespace BagistoPlus\Visual\Actions;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Shop\Http\Controllers\API\CompareController;

class ClearCompareList
{
    public function __construct(protected CompareController $compareApi) {}

    /**
     * Remove all items from compare list
     */
    public function execute()
    {
        /** @var JsonResource */
        $response = $this->compareApi->destroyAll();

        return $response->resolve();
    }
}
