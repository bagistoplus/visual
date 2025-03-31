<?php

namespace BagistoPlus\Visual\Actions\Checkout;

use BagistoPlus\Visual\Requests\StoreCartAddressesRequest;
use Webkul\Shop\Http\Controllers\API\AddressController;
use Webkul\Shop\Http\Controllers\API\OnepageController;
use Webkul\Shop\Http\Requests\Customer\AddressRequest;

class StoreAddresses
{
    public function __construct(protected AddressController $addressController, protected OnepageController $checkoutApi) {}

    public function execute(array $data)
    {
        request()->merge($data);
        $request = app(StoreCartAddressesRequest::class);

        foreach ($data as $key => $address) {
            if ($address['save_address']) {
                $address = array_merge($address, $this->saveCustomerAddress($address), ['use_for_shipping' => $address['use_for_shipping']]);
                $address['address'] = explode(PHP_EOL, $address['address']);
                $data[$key] = $address;
            }
        }

        $request->merge($data);
        $response = $this->checkoutApi->storeAddress($request);

        return $response->response()->getData(true);
    }

    protected function saveCustomerAddress(array $data): array
    {
        request()->merge($data);

        $request = app(AddressRequest::class);

        if (empty($data['id'])) {
            $response = $this->addressController->store($request);
        } else {
            $response = $this->addressController->update($request);
        }

        return $response->resolve()['data']->resource->toArray();
    }
}
