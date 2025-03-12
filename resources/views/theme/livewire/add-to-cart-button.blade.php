@php
  $text = $attributes->get('text', 'Add to cart');
  $icon = $attributes->get('icon', 'lucide-shopping-cart');
@endphp

<x-shop::ui.button
  wire:loading.attr="disabled"
  :wire:target="$action"
  icon="lucide-shopping-cart"
  {{ $attributes->merge(['x-on:click.prevent' => '$wire.' . $action . '()']) }}
>
  {{ $text }}
</x-shop::ui.button>
