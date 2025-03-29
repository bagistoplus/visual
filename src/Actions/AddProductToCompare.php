<?php

namespace BagistoPlus\Visual\Actions;

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
            $responseData = $response->resolve();

            session()->flash('success', $responseData['message']);
        } elseif ($response instanceof JsonResponse) {
            $responseData = $response->getData(true)['data'];

            session()->flash('warning', $responseData['message']);
        }
    }
}
