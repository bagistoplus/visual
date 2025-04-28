<?php

namespace BagistoPlus\Visual\Components\Livewire;

use BagistoPlus\Visual\Enums\Events;
use BagistoPlus\Visual\Support\InteractsWithCart;
use Livewire\Attributes\On;
use Livewire\Component;

#[On(Events::CART_UPDATED)]
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

        $this->open = $this->getItemsCount() > 0;

        $this->dispatch(Events::CART_UPDATED);
    }

    public function render()
    {
        return view('shop::components.cart-preview');
    }
}
