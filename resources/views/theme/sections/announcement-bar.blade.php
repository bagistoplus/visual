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
      <div>
        category: {{ $section->settings->category->name }}
      </div>
    @endif

    @if ($section->settings->product)
      <div>
        Product: {{ $section->settings->product->name }}
      </div>
    @endif

    @if ($section->settings->page)
      <div>
        Page: {{ $section->settings->page->page_title }}
      </div>
    @endif

    <div>
      Link: {{ $section->settings->button_link }}
    </div>

    @if ($section->settings->content)
      <div class="prose prose-md prose-stone">
        {!! $section->settings->content !!}
      </div>
    @endif
  </div>
@endif
