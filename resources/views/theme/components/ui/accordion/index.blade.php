@props(['value' => [], 'multiple' => false])

@php
  $props = [
      'value' => $value,
      'multiple' => $multiple,
  ];
@endphp
<div x-accordion='@json($props)' {{ $attributes }}>
  {{ $slot }}
</div>
