@php
  $positionValues = [
      'left' => 'text-left',
      'center' => 'text-center',
      'right' => 'text-right',
  ];
@endphp

@if (true)
  <div class="mb-4 flex bg-purple-500 px-4 py-1 text-white">
    @forelse($section->blocks as $block)
      <div>
        {{ $block->settings->text }}
      </div>
    @empty
      @if ($section->settings->announcement)
        @for ($i = 0; $i < $section->settings->count; $i++)
          <div>{{ $section->settings->announcement }} {{ $i }}</div>
        @endfor
      @endif
    @endif
  </div>
@endif
