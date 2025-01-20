@php
  $headingSize = [
      'small' => 'text-2xl',
      'medium' => 'text-3xl',
      'large' => 'text-4xl',
  ];

  $mobileGridClass = [
      '1' => 'grid-cols-1',
      '2' => 'grid-cols-2',
  ];

  $desktopGridClass = [
      '1' => 'md:grid-cols-1',
      '2' => 'md:grid-cols-2',
      '3' => 'md:grid-cols-3',
      '4' => 'md:grid-cols-4',
      '5' => 'md:grid-cols-5',
      '6' => 'md:grid-cols-6',
  ];
@endphp

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
  @if ($section->settings->heading)
    <h2 class="{{ $headingSize[$section->settings->heading_size] }} mb-12 text-center font-bold text-neutral-600">
      {{ $section->settings->heading }}
    </h2>
  @endif

  <div
    class="{{ $mobileGridClass[$section->settings->columns_mobile] }} {{ $desktopGridClass[$section->settings->columns_desktop] }} grid gap-8">
    @foreach ($section->blocks as $block)
      <div class="bg-surface-alt group relative h-64 cursor-pointer overflow-hidden rounded">
        <div class="absolute inset-0 z-10 bg-black/20 transition-colors group-hover:bg-black/40"></div>
        @php($image = $block->settings->category->banner_url ?? $block->settings->category->logo_url)
        @if ($image)
          <img src="{{ $image }}" alt="{{ $block->settings->category->name }}"
            class="h-full w-full object-cover object-center" />
        @endif
        <div class="absolute inset-0 z-20 flex items-center justify-center">
          <h3 class="text-2xl font-bold text-white">{{ $block->settings->category->name }}</h3>
        </div>
        <a href="{{ $block->settings->category->url }}" class="absolute inset-0 z-30"
          aria-label="{{ $block->settings->category->name }}"></a>
      </div>
    @endforeach
  </div>
</section>
