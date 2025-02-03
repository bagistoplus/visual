@php
  $text = $attributes->get('text', 'Add to cart');
  $variant = $attributes->get('variant', 'primary');
  $isSimple = $attributes->has('simple');
  $fullwidth = $attributes->has('fullwidth');

  $classes = match ($variant) {
      'primary' => 'bg-primary text-primary-100',
      'secondary' => 'bg-secondary text-secondary-100',
      'accent' => 'bg-accent text-accent-100',
      'primary-outline' => 'bg-surface ring-2 ring-primary text-primary hover:bg-primary-50',
  };

  $classes .= $isSimple ? ' p-3' : ' px-4 py-2';

  if ($fullwidth) {
      $classes .= ' w-full';
  }
@endphp

<button
  x-data="{ qty: $wire.entangle('quantity') }"
  x-modelable="qty"
  wire:click="{{ $action }}"
  wire:loading.attr="disabled"
  class="{{ $classes }} relative flex items-center justify-center rounded-full transition-opacity hover:opacity-90"
  {{ $attributes }}
>
  <x-lucide-shopping-cart
    class="h-5 w-5"
    wire:loading.class="text-transparent"
    aria-label="add to cart"
  />

  @if (!$isSimple)
    <span class="ml-3" wire:loading.class="text-transparent">
      {{ $text }}
    </span>
  @endif

  <div
    wire:loading
    wire:loading.class="!flex"
    class="absolute inset-0 h-full w-full items-center justify-center"
  >
    <x-lucide-loader-2 class="h-5 w-5 animate-spin" />
  </div>
</button>
