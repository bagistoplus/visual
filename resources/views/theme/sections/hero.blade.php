@php
  $heightMap = [
      'small' => '300px',
      'medium' => '400px',
      'large' => '500px',
  ];

  $contentPositionMap = [
      'top' => 'items-start',
      'middle' => 'items-center',
      'bottom' => 'items-end',
  ];
@endphp

<div class="relative" style="height: {{ $heightMap[$section->settings->height] }}">
  <div class="absolute inset-0 flex h-full w-full">
    @if ($section->settings->image)
      <img src="{{ $section->settings->image }}" alt="Summer collection background" class="h-full w-full object-cover" />
    @endif
  </div>

  @if ($section->settings->show_overlay)
    <div class="absolute inset-0 h-full w-full bg-black" style="opacity: {{ $section->settings->overlay_opacity }}%">
    </div>
  @endif

  <div
    class="z-5 {{ $contentPositionMap[$section->settings->content_position] }} relative flex h-full justify-center py-6">
    <div class="relative max-w-2xl px-8 py-8 text-neutral-100">

      @foreach ($section->blocks as $block)
        @if ($block->type === 'heading')
          <h1 class="text-center text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
            {{ $block->settings->heading }}
          </h1>
        @elseif($block->type === 'subheading')
          <p class="mx-auto mt-6 text-center text-xl text-neutral-200">
            {{ $block->settings->subheading }}
          </p>
        @elseif($block->type === 'button')
          <div class="mt-6 space-x-4 text-center">
            <a href="{{ $block->settings->link }}"
              class="bg-primary-50 hover:bg-primary-100 text-primary-600 inline-block rounded-lg border border-transparent px-8 py-3 text-base font-medium transition-colors duration-200">
              {{ $block->settings->text }}
            </a>
          </div>
        @endif
      @endforeach
    </div>
  </div>
</div>
