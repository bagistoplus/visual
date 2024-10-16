@php
  $positionValues = [
      'left' => 'text-left',
      'center' => 'text-center',
      'right' => 'text-right',
  ];
@endphp

@if (true)
  <div class="mb-4 bg-[var(--bg-color)] px-4 py-1 text-white" style="--bg-color: {{ $section->settings->color }}">
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

    @if ($section->settings->image)
      <img src={{ $section->settings->image }} class="w-24" />
    @endif

    @if ($section->settings->category)
      category: {{ $section->settings->category->name }}
    @endif
  </div>
@endif
