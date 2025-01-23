@props([
    'defaultOpen' => [0],
])

<div x-data="{ active: @json($defaultOpen) }" {{ $attributes->merge(['class' => 'space-y-4']) }}>
  {{ $slot }}
</div>
