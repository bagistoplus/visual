@php
  $menuItems = [
      ['name' => 'Shop', 'path' => '/shop'],
      ['name' => 'About', 'path' => '/about'],
      ['name' => 'Journal', 'path' => '/journal'],
      ['name' => 'FAQ', 'path' => '/faq'],
      ['name' => 'Contact', 'path' => '/contact'],
  ];

  $categories = $getCategories();
@endphp

<header class="bg-surface sticky top-0 z-50 w-full border-b" x-data="{ showMobileMenu: false, showSearch: false }">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 items-center justify-between text-neutral-700">
      <button class="-ml-2 p-2 transition-colors sm:hidden" aria-label="Open menu" @click="showMobileMenu = true">
        <x-heroicon-s-bars-3 class="h-6 w-6" />
      </button>

      @foreach ($section->blocks as $block)
        @if ($block->type === 'logo')
          <div class="flex items-center">
            <a href="/" class="truncate text-2xl font-medium">
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
          <div class="hidden items-center space-x-8 sm:flex">
            @if ($categories->count() > 1)
              @foreach ($getCategories() as $category)
                <a href="{{ url($category->slug) }}" class="hover:text-primary transition-colors">
                  {{ $category['name'] }}
                </a>
              @endforeach
            @else
              @foreach ($menuItems as $menu)
                <a href="{{ $menu['path'] }}" class="hover:text-primary transition-colors">
                  {{ $menu['name'] }}
                </a>
              @endforeach
            @endif
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
          @endif
        @endforeach
      </div>
    </div>
  </div>

  <!-- Mobile Menu -->
  <x-shop::mobile-menu :categories="$categories" />
</header>
