<?php

use BagistoPlus\Visual\Requests\StoreCartAddressesRequest;

it('labels the phone field without a Bagisto key that 2.5 renamed', function () {
    $attributes = (new StoreCartAddressesRequest)->attributes();

    expect($attributes['billing.phone'])->toBe('Phone')
        ->and($attributes['shipping.phone'])->toBe('Phone');
});
