@props(['id', 'productId', 'quantityProp' => 'quantity'])

<x-shop::ui.button
  wire:loading.attr="disabled"
  variant="primary"
  size="sm"
  wire:target="loader({{ $id }})"
  x-on:click.prevent="$wire.moveToCart({{ $id }}, {{ $productId }}, {{ $quantityProp }}); $wire.loader({{ $id }});"
>
  {{ trans('shop::app.customers.account.wishlist.move-to-cart') }}
</x-shop::ui.button>
