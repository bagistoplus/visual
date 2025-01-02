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

  $buttonStyles = [
      'primary' => 'bg-primary text-white',
      'secondary' => 'border bg-gray-100 text-gray-700 hover:shadow-lg hover:bg-gray-200',
  ];
@endphp

<div class="relative" style="height: {{ $heightMap[$section->settings->height] }}">
  <div class="absolute inset-0 flex h-full w-full">
    @if ($section->settings->image)
      <img
        src="https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80"
        alt="Summer collection background" class="h-full w-full object-cover" />
    @endif
  </div>

  @if ($section->settings->show_overlay)
    <div class="absolute inset-0 h-full w-full bg-black" style="opacity: {{ $section->settings->overlay_opacity }}%">
    </div>
  @endif

  <div
    class="z-5 {{ $contentPositionMap[$section->settings->content_position] }} relative flex h-full justify-center py-6">
    <div class="relative max-w-2xl px-8 py-8 text-white">

      @foreach ($section->blocks as $block)
        @if ($block->type === 'heading')
          <h1 class="text-center text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
            {{ $block->settings->heading }}
          </h1>
        @elseif($block->type === 'subheading')
          <p class="mx-auto mt-6 text-center text-xl text-gray-100">
            {{ $block->settings->subheading }}
          </p>
        @elseif($block->type === 'button')
          <div class="mt-6 space-x-4 text-center">
            <a href="{{ $block->settings->link }}"
              class="inline-block rounded-md border border-transparent bg-white px-8 py-3 text-base font-medium text-gray-900 transition-colors duration-200 hover:bg-gray-100">
              {{ $block->settings->text }}
            </a>
          </div>
        @endif
      @endforeach
    </div>
  </div>
</div>
