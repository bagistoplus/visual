@props([
    'label' => null,
    'value' => 1,
    'min' => 0,
])

@php
  $props = ['value' => intval($value), 'min' => $min];
@endphp

<div
  x-data
  x-number-input="@js($props)"
  {{ $attributes->merge(['class' => 'flex items-center space-x-4']) }}
>
  @if ($label)
    <labe x-input-number:label class="text-sm font-medium">
      {{ $label }}
    </labe>
  @endif
  <div class="focus-within:ring-primary flex items-center rounded-md border border-neutral-300 focus-within:ring-2">
    <button x-number-input:decrement-trigger class="hover:text-primary disabled:hover:text-neutral cursor-pointer p-1 transition-colors disabled:cursor-default">
      <x-lucide-minus class="h-4 w-4" />
      </svg>
    </button>

    <input x-number-input:input class="w-16 appearance-none rounded-none border-0 py-0.5 text-center text-sm focus:ring-0">

    <button x-number-input:increment-trigger class="hover:text-primary disabled:hover:text-neutral disable:cursor-default p-1 transition-colors">
      <x-lucide-plus class="h-4 w-4" />
    </button>
  </div>
</div>
