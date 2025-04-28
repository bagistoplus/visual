@props(['placement' => 'end'])

@php
  $props = ['placement' => $placement];
@endphp

<div
  x-data
  x-dropdown="@js($props)"
  {{ $attributes->merge(['class' => 'relative']) }}
>
  {{ $slot }}
</div>
