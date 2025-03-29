@props(['cart' => []])

@php
  $checkoutButons = view_render_event('bagisto.shop.checkout.payment.' . $cart->payment_method, [
      'cart' => $cart,
  ]);
@endphp

<div {{ $attributes }}>
  @if ($checkoutButons)
    {!! $checkoutButons !!}
  @else
    <button
      wire:click="placeOrder"
      wire:target="placeOrder"
      wire:loading.attr="disabled"
      class="bg-primary text-primary-50 ring-primary relative rounded-lg px-6 py-2 ring-offset-2 hover:opacity-90 focus:ring-2"
    >
      <span wire:target="placeOrder" wire:loading.class="text-transparent">
        @lang('shop::app.checkout.onepage.summary.place-order')
      </span>

      <div
        wire:loading.flex
        wire:target="placeOrder"
        class="absolute inset-0 h-full w-full items-center justify-center"
      >
        <x-lucide-loader-2 class="h-5 w-5 animate-spin" />
      </div>
    </button>
  @endif
</div>
