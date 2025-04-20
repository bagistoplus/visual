<?php

namespace BagistoPlus\Visual\Components\Livewire;

use BagistoPlus\Visual\Actions\Cart\AddProductToCart;
use BagistoPlus\Visual\Enums\Events;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AddToCartButton extends Component
{
    public string $action = 'addToCart';

    #[Locked]
    public $productId;

    public $quantity = 1;

    public function addToCart()
    {
        $result = app(AddProductToCart::class)->execute([
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
        ]);

        if ($result['success']) {
            session()->flash('success', $result['message']);
            $this->dispatch(Events::CART_UPDATED);
        } else {
            session()->flash('error', $result['message']);
            $this->redirect($result['redirect_url']);
        }
    }

    public function render()
    {
        return view('shop::livewire.add-to-cart-button');
    }
}
