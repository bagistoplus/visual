<?php

namespace BagistoPlus\Visual\Components\Livewire;

use BagistoPlus\Visual\Actions\Cart\AddProductToWishlist;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AddToWishlistButton extends Component
{
    #[Locked]
    public $productId;

    #[Locked]
    public $inUserWishlist = false;

    public function handle()
    {
        $response = app(AddProductToWishlist::class)->execute($this->productId);

        if (isset($response['message'])) {
            session()->flash('info', $response['message']);
        }

        $this->inUserWishlist = auth('customer')
            ->user()?->wishlist_items
            ->where('channel_id', app('core')->getCurrentChannel()->id)
            ->where('product_id', $this->productId)
            ->count();
    }

    public function render()
    {
        return view('shop::livewire.add-to-wishlist-button');
    }
}
