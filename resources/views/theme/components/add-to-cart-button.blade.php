@php
  $action = $attributes->get('action', 'addToCart');
  $text = $attributes->get('text', 'Add to cart');
  $variant = $attributes->get('variant', 'primary');
  $isSimple = $attributes->has('simple');
  $fullwidth = $attributes->has('fullwidth');

  $classes = match ($variant) {
      'primary'
          => 'bg-primary text-primary-100 disabled:bg-neutral-200 disabled:text-neutral-400 disabled:hover:bg-neutral-200',
      'secondary'
          => 'bg-secondary text-secondary-100 disabled:bg-neutral-200 disabled:text-neutral-400 disabled:hover:bg-neutral-200',
      'accent'
          => 'bg-accent text-accent-100 disabled:bg-neutral-200 disabled:text-neutral-400 disabled:hover:bg-neutral-200',
      'primary-outline'
          => 'bg-surface ring-2 ring-primary text-primary hover:bg-primary-50 disabled:ring-neutral-200 disabled:text-neutral-400 disabled:hover:bg-transparent',
  };

  $classes .= $isSimple ? ' p-3' : ' px-4 py-2';

  if ($fullwidth) {
      $classes .= ' w-full';
  }
@endphp

<button
  wire:click.prevent="{{ $action }}"
  wire:target="{{ $action }}"
  wire:loading.attr="disabled"
  class="{{ $classes }} relative flex items-center justify-center rounded-full transition-opacity hover:opacity-90 disabled:cursor-not-allowed"
  {{ $attributes }}
>
  <x-lucide-shopping-cart
    class="h-5 w-5"
    wire:loading.class="text-transparent"
    aria-label="add to cart"
  />

  @if (!$isSimple)
    <span
      class="ml-3"
      wire:target="{{ $action }}"
      wire:loading.class="text-transparent"
    >
      {{ $text }}
    </span>
  @endif

  <div
    wire:loading
    wire:target="{{ $action }}"
    wire:loading.class="!flex"
    class="absolute inset-0 h-full w-full items-center justify-center"
  >
    <x-lucide-loader-2 class="h-5 w-5 animate-spin" />
  </div>
</button>
