@props([
    // Allowed values: bottom-start, bottom-end, top-start, or top-end (default: bottom-start)
    'placement' => 'bottom-start',
])

<div
  x-data
  x-dropdown
  class="relative"
>
  <!-- Trigger slot -->
  <div x-dropdown:trigger class="contents">
    {{ $trigger }}
  </div>

  <!-- Dropdown content slot -->
  <div x-dropdown:content x-cloak>
    {{ $slot }}
  </div>
</div>
