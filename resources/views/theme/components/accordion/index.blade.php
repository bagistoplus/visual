@props([
    'defaultOpen' => [0],
])

<div x-data="{ active: @js($defaultOpen) }" {{ $attributes->merge(['class' => 'space-y-4']) }}>
  {{ $slot }}
</div>
