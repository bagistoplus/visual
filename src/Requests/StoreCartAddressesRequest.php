<?php

namespace BagistoPlus\Visual\Requests;

use Webkul\Shop\Http\Requests\CartAddressRequest;

class StoreCartAddressesRequest extends CartAddressRequest
{
    public function attributes()
    {
        $attributes = [];
        $addressTypes = ['billing', 'shipping'];

        foreach ($addressTypes as $addressType) {
            $attributes = array_merge($attributes, [
                "$addressType.first_name" => trans('shop::app.checkout.onepage.address.first-name'),
                "$addressType.last_name" => trans('shop::app.checkout.onepage.address.last-name'),
                "$addressType.email" => trans('shop::app.checkout.onepage.address.email'),
                "$addressType.address" => trans('shop::app.checkout.onepage.address.street-address'),
                "$addressType.city" => trans('shop::app.checkout.onepage.address.street-address'),
                "$addressType.country" => trans('shop::app.checkout.onepage.address.country'),
                "$addressType.state" => trans('shop::app.checkout.onepage.address.state'),
                "$addressType.postcode" => trans('shop::app.checkout.onepage.address.city'),
                "$addressType.phone" => trans('shop::app.checkout.onepage.address.postcode'),
            ]);
        }

        return $attributes;
    }
}
