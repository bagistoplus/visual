<?php

namespace BagistoPlus\Visual\Components;

use BagistoPlus\Visual\Actions\AddProductToCart;
use Livewire\Component;

class AddToCartButton extends Component
{
    public bool $simple = false;

    public string $variant = 'primary';

    public bool $fullwidth = false;

    public $productId;

    public $quantity = 1;

    public function handle()
    {
        $result = app(AddProductToCart::class)->execute([
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
        ]);

        if ($result['success']) {
            session()->flash('success', $result['message']);
            $this->dispatch('cartUpdated');
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
