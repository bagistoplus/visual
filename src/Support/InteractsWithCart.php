<?php

namespace BagistoPlus\Visual\Support;

use Webkul\Checkout\Facades\Cart;
use Webkul\Shop\Http\Resources\CartResource;

trait InteractsWithCart
{
    public function getCart()
    {
        // @phpstan-ignore-next-line
        return Cart::getCart();
    }

    public function getCartResource()
    {
        $cart = $this->getCart();

        return $cart
            ? (new CartResource($cart))->response()->getData()->data
            : null;
    }

    public function cartHasErrors()
    {
        // @phpstan-ignore-next-line
        return Cart::hasError();
    }

    public function getItemsCount()
    {
        $cart = $this->getCartResource();

        if (! $cart) {
            return 0;
        }

        if (app('core')->getConfigData('sales.checkout.my_cart.summary') === 'display_item_quantity') {
            return intval($cart->items_qty);
        }

        return intval($cart->items_count);
    }

    public function isCartEmpty()
    {
        return $this->getItemsCount() === 0;
    }

    public function getCartItems()
    {
        return $this->getCartResource()->items;
    }

    public function cartHaveStockableItems()
    {
        return $this->getCart()?->haveStockableItems();
    }

    public function updateCartItemQuantity($itemId, $quantity)
    {
        if ($quantity <= 0) {
            return $this->removeCartItem($itemId);
        }

        Cart::updateItems([
            'qty' => [$itemId => $quantity],
        ]);
    }

    public function removeCartItem($itemId)
    {
        Cart::removeItem($itemId);
        Cart::collectTotals();

        session()->flash('success', __('shop::app.checkout.cart.success-remove'));
    }

    public function shouldDisplayCartSubtotalIncludingTax()
    {
        return app('core')->getConfigData('sales.taxes.shopping_cart.display_subtotal') === 'including_tax';
    }

    public function shouldDisplayCartBothSubtotals()
    {
        return app('core')->getConfigData('sales.taxes.shopping_cart.display_subtotal') === 'both';
    }

    public function shouldDisplayCartPricesIncludingTax()
    {
        return app('core')->getConfigData('sales.taxes.shopping_cart.display_prices') === 'including_tax';
    }

    public function shouldDisplayCartBothPrices()
    {
        return app('core')->getConfigData('sales.taxes.shopping_cart.display_prices') === 'both';
    }

    public function getFormattedCartSubtotalWithTax()
    {
        return app('core')->formatPrice($this->getCart()->sub_total_incl_tax);
    }

    public function getFormattedCartSubtotal()
    {
        return app('core')->formatPrice($this->getCart()->sub_total);
    }
}
