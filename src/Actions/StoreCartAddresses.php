<?php

namespace BagistoPlus\Visual\Actions;

use Webkul\Checkout\Facades\Cart;
use Webkul\Shop\Http\Controllers\API\AddressController;
use Webkul\Shop\Http\Requests\Customer\AddressRequest;

class StoreCartAddresses
{
    public function __construct(protected AddressController $addressController) {}

    public function execute(array $data)
    {
        foreach ($data as $key => $address) {
            if ($address['save_address']) {
                $address = array_merge($address, $this->saveCustomerAddress($address), ['use_for_shipping' => $address['use_for_shipping']]);
                $address['address'] = explode(PHP_EOL, $address['address']);
                $data[$key] = $address;
            }
        }

        Cart::saveAddresses($data);
        Cart::collectTotals();

        return $data;
    }

    protected function saveCustomerAddress(array $data): array
    {
        $request = new AddressRequest;
        $request->merge($data);

        // inside AddressController::update, request() is used instead of the form request
        request()->merge($request->all());

        if (isset($data['id'])) {
            $response = $this->addressController->update($request);
        } else {
            $response = $this->addressController->store($request);
        }

        return $response->toArray($request)['data']->resource->toArray();
    }
}
