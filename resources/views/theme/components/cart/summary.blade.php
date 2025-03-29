@props(['cart'])

@php
  $displayTax = [
      'prices' => core()->getConfigData('sales.taxes.shopping_cart.display_prices'),
      'subtotal' => core()->getConfigData('sales.taxes.shopping_cart.display_subtotal'),
      'shipping' => core()->getConfigData('sales.taxes.shopping_cart.display_shipping_amount'),
  ];
@endphp

<div class="space-y-4">
  <!-- Sub Total -->
  @if ($displayTax['subtotal'] === 'including_tax')
    <div class="flex justify-between">
      <span class="text-neutral-600">
        @lang('shop::app.checkout.cart.summary.sub-total')
      </span>
      <span class="text-neutral-900">
        {{ $cart->formatted_sub_total_incl_tax }}
      </span>
    </div>
  @elseif ($displayTax['subtotal'] === 'both')
    <div class="flex justify-between">
      <span class="text-neutral-600">
        @lang('shop::app.checkout.cart.summary.sub-total-excl-tax')
      </span>
      <span class="text-neutral-900">
        {{ $cart['formatted_sub_total'] }}
      </span>
    </div>

    <div class="flex justify-between">
      <span class="text-neutral-600">
        @lang('shop::app.checkout.cart.summary.sub-total-incl-tax')
      </span>
      <span class="text-neutral-900">
        {{ $cart->formatted_sub_total_incl_tax }}
      </span>
    </div>
  @else
    <div class="flex justify-between">
      <span class="text-neutral-600">
        @lang('shop::app.checkout.cart.summary.sub-total')
      </span>
      <span class="text-neutral-900">
        {{ $cart->formatted_sub_total }}
      </span>
    </div>
  @endif

  <!-- Apply coupon -->
  <div class="border-y border-neutral-300 py-4">
    <livewire:cart-coupon-form />
  </div>

  <!-- Discount amount -->
  @if ($cart->discount_amount)
    <div class="flex justify-between">
      <span class="text-neutral-600">
        @lang('shop::app.checkout.onepage.summary.discount-amount')
      </span>
      <span class="text-neutral-900">
        {{ $cart->formatted_discount_amount }}
      </span>
    </div>
  @endif

  <!-- Shipping Rates -->
  @if ($displayTax['shipping'] === 'including_tax')
    <div class="flex justify-between">
      <span class="text-neutral-600">
        @lang('shop::app.checkout.cart.summary.delivery-charges')
      </span>
      <span class="text-neutral-900">
        {{ $cart->formatted_shipping_amount_incl_tax }}
      </span>
    </div>
  @elseif ($displayTax['shipping'] === 'both')
    <div class="flex justify-between">
      <span class="text-neutral-600">
        @lang('shop::app.checkout.cart.summary.delivery-charges-excl-tax')
      </span>
      <span class="text-neutral-900">
        {{ $cart->formatted_shipping_amount }}
      </span>
    </div>

    <div class="flex justify-between">
      <span class="text-neutral-600">
        @lang('shop::app.checkout.cart.summary.delivery-charges-incl-tax')
      </span>
      <span class="text-neutral-900">
        {{ $cart->formatted_shipping_amount_incl_tax }}
      </span>
    </div>
  @else
    <div class="flex justify-between">
      <span class="text-neutral-600">
        @lang('shop::app.checkout.cart.summary.delivery-charges')
      </span>
      <span class="text-neutral-900">
        {{ $cart->formatted_shipping_amount }}
      </span>
    </div>
  @endif

  <!-- Taxes -->
  <div class="border-t border-neutral-200 pt-4" x-data="{ showTaxes: false }">
    <div class="flex justify-between">
      <span class="text-neutral-600">
        @lang('shop::app.checkout.cart.summary.tax')
      </span>

      <div class="space-x-1">
        <span class="text-neutral-900">
          {{ $cart->formatted_tax_total }}
        </span>
        @if ($cart->tax_total)
          <button type="button" x-on:click="showTaxes = !showTaxes">
            <x-lucide-chevron-down class="h-4 w-4" />
          </button>
        @endif
      </div>
    </div>

    @if ($cart->tax_total)
      <div x-show="showTaxes">
        @foreach ($cart->applied_taxes as $tax => $amount)
          <div class="mt-1 flex justify-between pr-6 text-xs">
            <span class="text-neutral-600">
              {{ $tax }}
            </span>
            <span class="text-neutral-900">
              {{ $amount }}
            </span>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  <!-- Cart Grand Total -->
  <div class="border-t border-neutral-200 pt-4">
    <div class="flex justify-between">
      <span class="text-lg font-medium text-neutral-900">
        @lang('shop::app.checkout.cart.summary.grand-total')
      </span>
      <span class="text-lg font-medium text-neutral-900">
        {{ $cart->formatted_grand_total }}
      </span>
    </div>
  </div>
</div>
