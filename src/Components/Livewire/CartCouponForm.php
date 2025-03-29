<?php

namespace BagistoPlus\Visual\Components\Livewire;

use BagistoPlus\Visual\Enums\Events;
use BagistoPlus\Visual\Support\InteractsWithCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Livewire\Component;
use Webkul\Shop\Http\Controllers\API\CartController;

class CartCouponForm extends Component
{
    use InteractsWithCart;

    public $couponCode;

    public function applyCoupon()
    {
        request()->merge(['code' => $this->couponCode]);
        $response = app(CartController::class)->storeCoupon();

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            session()->flash('warning', $data['message']);
        } elseif ($response instanceof JsonResource) {
            $data = $response->toArray(request());
            session()->flash('success', $data['message']);
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
            'cart' => $this->getCart(),
        ]);
    }
}
