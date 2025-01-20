<div x-data="{ show: true }" x-show="show"
  class="bg-primary text-primary-200 relative truncate px-4 py-2 text-center text-sm">
  @if ($section->settings->link)
    <a href="{{ $section->settings->link }}" class="hover:underline">{{ $section->settings->text }}</a>
    @svg('heroicon-o-arrow-right', ['class' => 'w-4 h-4 inline-block'])
  @else
    <p>{{ $section->settings->text }}</p>
  @endif
  <button class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:opacity-75" aria-label="Close" @click="show = false">
    @svg('heroicon-o-x-mark', ['class' => 'w-4 h-4'])
  </button>
</div>
