@php
  $positionValues = [
      'left' => 'text-left',
      'center' => 'text-center',
      'right' => 'text-right',
  ];
@endphp

@if (true)
  <div class="bg-primary text-on-primary {{ $positionValues['center'] }} px-4 py-1">
    Announcement
  </div>
@endif
