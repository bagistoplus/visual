<?php

namespace BagistoPlus\Visual\Components\Livewire;

use BagistoPlus\Visual\Support\InteractsWithCart;
use Livewire\Attributes\On;
use Livewire\Component;

#[On('cartUpdated')]
class CartPreview extends Component
{
    use InteractsWithCart;

    public $open = false;

    public function updateItemQuantity($itemId, $quantity)
    {
        $this->updateCartItemQuantity($itemId, $quantity);
    }

    public function removeItem($itemId)
    {
        $this->removeCartItem($itemId);
        $this->open = false;
        $this->dispatch('cartUpdated');
    }

    public function render()
    {
        return view('shop::components.cart-preview');
    }
}
