@props([
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'gap' => 0,
    'value' => [0, 100],
])

@php
  $props = [
      'min' => $min,
      'max' => $max,
      'step' => $step,
      'minStepsBetweenThumbs' => $gap,
      'value' => $value,
  ];
@endphp

<div
  x-data
  x-slider="@js($props)"
  {{ $attributes }}
>
  <div x-slider:root class="relative">
    <div x-slider:control class="relative h-1">
      <div x-slider:track class="absolute inset-0 rounded bg-neutral-200">
        <div x-slider:range class="bg-primary absolute h-full rounded"></div>
      </div>

      <!-- Thumb 1 -->
      <div x-slider:thumb="0" class="bg-primary border-primary absolute top-1/2 h-4 w-4 -translate-x-1/2 -translate-y-1/2 cursor-pointer rounded-full border">
        <input x-slider:hidden-input="0" />
      </div>

      <!-- Thumb 2 -->
      <div x-slider:thumb="1" class="bg-primary border-primary absolute top-1/2 h-4 w-4 -translate-x-1/2 -translate-y-1/2 cursor-pointer rounded-full border">
        <input x-slider:hidden-input="1" />
      </div>

    </div>
    <div class="flex items-center justify-between py-5">
      <div class="relative">
        <span class="absolute left-2 top-1/2 -translate-y-1/2 transform">{{ core()->getCurrentCurrency()->symbol }}</span>
        <input
          x-slider:input="0"
          aria-label="Min value"
          class="w-24 rounded border border-gray-200 py-1 pl-6 pr-2 text-right text-sm"
        >
      </div>

      <div class="relative">
        <span class="absolute left-2 top-1/2 -translate-y-1/2 transform">{{ core()->getCurrentCurrency()->symbol }}</span>
        <input
          x-slider:input="1"
          aria-label="Max value"
          class="w-24 rounded border border-gray-200 py-1 pl-6 pr-2 text-right text-sm"
        >
      </div>
    </div>
  </div>
</div>
