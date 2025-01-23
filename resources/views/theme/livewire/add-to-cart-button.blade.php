@php
  $classes = match ($variant) {
      'primary' => 'bg-primary text-primary-100',
      'secondary' => 'bg-secondary text-secondary-100',
      'accent' => 'bg-accent text-accent-100',
  };

  $classes .= $simple ? ' p-3' : ' px-4 py-2';

  if ($fullwidth) {
      $classes .= ' w-full';
  }
@endphp

<button wire:click="handle" wire:loading.attr="disabled"
  class="{{ $classes }} relative flex items-center justify-center rounded-full transition-opacity hover:opacity-90">
  <x-lucide-shopping-cart class="h-5 w-5" wire:loading.class="text-transparent" aria-label="add to cart" />

  @if (!$simple)
    <span class="ml-3" wire:loading.class="text-transparent">Add to cart</span>
  @endif

  <div wire:loading wire:loading.class="!flex" class="absolute inset-0 h-full w-full items-center justify-center">
    <x-lucide-loader-2 class="h-5 w-5 animate-spin" />
  </div>
</button>
