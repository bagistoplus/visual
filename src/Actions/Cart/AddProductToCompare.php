<?php

namespace BagistoPlus\Visual\Actions\Cart;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Shop\Http\Controllers\API\CompareController;

class AddProductToCompare
{
    protected CompareController $compareApi;

    public function __construct(CompareController $compareApi)
    {
        $this->compareApi = $compareApi;
    }

    public function execute($productId)
    {
        request()->request->add([
            'product_id' => $productId,
        ]);

        $response = $this->compareApi->store();

        if ($response instanceof JsonResource) {
            return $response->resolve();
        } elseif ($response instanceof JsonResponse) {
            return $response->getData(true)['data'];
        }

        return $response;
    }
}
