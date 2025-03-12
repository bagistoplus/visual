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
        // We replace request body with $data to discard custom livewire inputs
        $originalRequestData = request()->request->all();
        request()->request->replace([
            'product_id' => $productId,
        ]);

        $response = $this->compareApi->store();

        // and then we restore livewire request data to prevent ignition crash
        // we should probably move this logic to some livewire middleware
        request()->request->replace($originalRequestData);

        if ($response instanceof JsonResource) {
            $responseData = $response->toArray(request());

            session()->flash('success', $responseData['message']);
        } elseif ($response instanceof JsonResponse) {
            $responseData = $response->getData(true)['data'];

            session()->flash('warning', $responseData['message']);
        }
    }
}
