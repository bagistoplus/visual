@props(['placement' => 'end'])

@php
  $props = ['placement' => $placement];
@endphp

<div x-dropdown="@js($props)" {{ $attributes->merge(['class' => 'relative']) }}>
  {{ $slot }}
</div>
