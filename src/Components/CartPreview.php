<?php

namespace BagistoPlus\Visual\Components;

use BagistoPlus\Visual\Support\InteractsWithCart;
use Livewire\Component;

class CartPreview extends Component
{
    use InteractsWithCart;

    public function updateItemQuantity($itemId, $quantity)
    {
        $this->updateCartItemQuantity($itemId, $quantity);
    }

    public function removeItem($itemId)
    {
        $this->removeCartItem($itemId);
    }

    public function render()
    {
        return view('shop::components.cart-preview');
    }
}
