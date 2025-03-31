<?php

namespace BagistoPlus\Visual\Components\Livewire;

use BagistoPlus\Visual\Actions\Cart\StoreCoupon;
use BagistoPlus\Visual\Enums\Events;
use BagistoPlus\Visual\Support\InteractsWithCart;
use Livewire\Component;
use Webkul\Shop\Http\Controllers\API\CartController;

class CartCouponForm extends Component
{
    use InteractsWithCart;

    public $couponCode;

    public function applyCoupon(StoreCoupon $storeCoupon)
    {
        $response = $storeCoupon->execute($this->couponCode);

        if (isset($response['message'])) {
            session()->flash($response['success'] ? 'success' : 'warning', $response['message']);
        }

        $this->dispatch(Events::COUPON_APPLIED);
    }

    public function removeCoupon()
    {
        $response = app(CartController::class)->destroyCoupon();
        $data = $response->toArray(request());

        session()->flash('success', $data['message']);

        $this->dispatch(Events::COUPON_REMOVED);
    }

    public function render()
    {
        return view('shop::livewire.coupon-form', [
            'cart' => $this->getCartResource(),
        ]);
    }
}
