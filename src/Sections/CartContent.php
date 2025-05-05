<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Enums\Events;
use BagistoPlus\Visual\Support\InteractsWithCart;
use Livewire\Attributes\On;

#[On(Events::SHIPPING_METHOD_SET)]
#[On(Events::COUPON_APPLIED)]
#[On(Events::COUPON_REMOVED)]
class CartContent extends LivewireSection
{
    use InteractsWithCart;

    protected static array $enabledOn = ['cart'];

    protected static string $view = 'shop::sections.cart-content';

    public $itemsSelected = [];

    public function updateItemQuantity($itemId, $quantity)
    {
        try {
            $this->updateCartItemQuantity($itemId, $quantity);
            $this->dispatch(Events::CART_UPDATED);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
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
            'cart' => $this->getCartResource(),
            'cartErrors' => $this->cartHasErrors(),
            'haveStockableItems' => $this->cartHaveStockableItems(),
        ];
    }
}
