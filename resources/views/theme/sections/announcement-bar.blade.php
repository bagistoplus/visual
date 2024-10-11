@php
  $positionValues = [
      'left' => 'text-left',
      'center' => 'text-center',
      'right' => 'text-right',
  ];
@endphp

@if (true)
  <div class="bg-primary text-on-primary {{ $positionValues['center'] }} mb-4 bg-purple-500 px-4 py-1 text-white">
    {{ $section['settings']['announcement'] }}
    {{ $section['settings']['count'] }}
    {{ $section['settings']['vertical_alignment'] }}
  </div>
@endif
