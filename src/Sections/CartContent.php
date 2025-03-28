<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Enums\Events;
use BagistoPlus\Visual\Support\InteractsWithCart;
use Livewire\Attributes\On;
use Webkul\Checkout\Facades\Cart;

#[On(Events::SHIPPING_METHOD_SET)]
#[On(Events::COUPON_APPLIED)]
#[On(Events::COUPON_REMOVED)]
class CartContent extends LivewireSection
{
    use InteractsWithCart;

    public static string $view = 'shop::sections.cart-content';

    public $itemsSelected = [];

    public function updateItemQuantity($itemId, $quantity)
    {
        $this->updateCartItemQuantity($itemId, $quantity);

        $this->dispatch(Events::CART_UPDATED);
    }

    public function removeItem($itemId)
    {
        $this->removeCartItem($itemId);
        $this->dispatch(Events::CART_UPDATED);
    }

    public function removeSelectedItems()
    {
        foreach ($this->itemsSelected as $itemId) {
            $this->removeCartItem($itemId);
        }

        $this->dispatch(Events::CART_UPDATED);
    }

    public function getViewData(): array
    {
        if ($this->isCartEmpty()) {
            return [];
        }

        return [
            'items' => $this->getCartItems(),
            'cartErrors' => Cart::getErrors(),
            'cartResource' => $this->getCartResource(),
        ];
    }
}
