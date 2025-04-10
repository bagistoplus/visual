@php
  $categories = $getCategories();
@endphp

<header class="bg-surface sticky top-0 z-20 w-full border-b" x-data="{ showSearch: false }">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 items-center justify-between gap-x-6 text-neutral-700">
      <x-shop::ui.drawer placement="start" title="Menu">
        <x-slot:trigger>
          <button class="-ml-2 p-2 transition-colors sm:hidden" aria-label="Open menu">
            <x-heroicon-s-bars-3 class="h-6 w-6" />
          </button>
        </x-slot:trigger>
        <div>
          <!-- Mobile Menu -->
          <x-shop::mobile-menu :categories="$categories" />
        </div>
      </x-shop::ui.drawer>

      @foreach ($section->blocks as $block)
        @if ($block->type === 'logo')
          <div class="flex items-center">
            <a href="{{ url('') }}" class="truncate text-2xl font-medium">
              @if ($block->settings->logo)
                <span class="sr-only">{{ $block->settings->logo_text ?? config('app.name') }}</span>
                <img src="{{ $block->settings->logo }}" alt="{{ $block->settings->logo_text ?? config('app.name') }}" />
              @elseif ($logo = core()->getCurrentChannel()->logo_url)
                <span class="sr-only">{{ config('app.name') }}</span>
                <img src="{{ $logo }}" alt="{{ config('app.name') }}" />
              @else
                {{ $block->settings->logo_text ?? config('app.name') }}
              @endif
            </a>
          </div>
        @elseif($block->type === 'nav')
          @php
            $classes = '';
            if ($block->settings->push_to_left) {
                $classes .= ' mr-auto';
            }

            if ($block->settings->push_to_right) {
                $classes .= ' ml-auto';
            }
          @endphp
          <div class="{{ $classes }} hidden h-full sm:block">
            <x-shop::navigation :categories="$categories" />
          </div>
        @endif
      @endforeach

      <div class="flex items-center space-x-4">
        @foreach ($section->blocks as $block)
          @if ($block->type === 'currency')
            <x-shop::currency-selector />
          @elseif ($block->type === 'locale')
            <x-shop::language-selector />
          @elseif ($block->type === 'search')
            <x-shop::search-form />
          @elseif ($block->type === 'user')
            <x-shop::user-menu />
          @elseif ($block->type === 'cart')
            <livewire:cart-preview />
          @elseif ($block->type === 'compare')
            @if (core()->getConfigData('catalog.products.settings.compare_option'))
              <a
                class="relative hidden items-center p-2 sm:flex"
                aria-label="@lang('shop::app.components.layouts.header.compare')"
                title="@lang('shop::app.components.layouts.header.compare')"
                href="{{ route('shop.compare.index') }}"
              >
                <x-lucide-arrow-left-right class="hover:text-primary h-5 w-5 transition-colors" />
                {{-- <span
                  class="bg-primary absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full text-xs text-white"
                >1</span> --}}
              </a>
            @endif
          @endif
        @endforeach
      </div>
    </div>
  </div>
</header>
