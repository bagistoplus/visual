@props(['categories'])

@php
  $menuItems = [
      ['name' => 'Shop', 'path' => '/shop'],
      ['name' => 'About', 'path' => '/about'],
      ['name' => 'Journal', 'path' => '/journal'],
      ['name' => 'FAQ', 'path' => '/faq'],
      ['name' => 'Contact', 'path' => '/contact'],
  ];
@endphp
<div x-cloak x-show="showMobileMenu" x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="transform -translate-x-full" x-transition:enter-end="transform translate-x-0"
  x-transition:leave="transition ease-in duration-300" x-transition:leave-start="transform translate-x-0"
  x-transition:leave-end="transform -translate-x-full" class="fixed inset-0 bg-black/50 sm:hidden"
  @click="showMobileMenu = false">
  <div x-cloak x-show="showMobileMenu" x-transition
    class="bg-surface fixed inset-y-0 left-0 w-4/5 max-w-sm transform transition-transform duration-300 ease-out"
    @click.stop>
    <div class="border-surface-600 flex h-16 items-center justify-between border-b px-4">
      <h2 class="text-lg font-medium">Menu</h2>
      <button class="hover:text-primary p-2 transition-colors" aria-label="Close menu" @click="showMobileMenu = false">
        <x-heroicon-o-x-mark class="h-6 w-6" />
      </button>
    </div>

    <nav class="px-2 py-4">
      @if ($categories->count() > 1)
        @foreach ($categories as $category)
          <a href="{{ url($category->slug) }}"
            class="text-primary-600 hover:text-primary hover:bg-surface-600/50 flex items-center justify-between rounded-lg px-4 py-3 transition-colors"
            @click="showMobileMenu = false">
            {{ $category->name }}
            <x-lucide-chevron-right class="h-5 w-5" />
          </a>
        @endforeach
      @else
        @foreach ($menuItems as $item)
          <a href="{{ $item['path'] }}"
            class="text-secondary hover:text-primary hover:bg-surface-alt flex items-center justify-between rounded-lg px-4 py-3 transition-colors"
            @click="showMobileMenu = false">
            {{ $item['name'] }}
            <x-heroicon-o-chevron-right class="h-5 w-5" />
          </a>
        @endforeach
      @endif

      <!-- Currency Selector - Mobile -->
      <div class="border-surface-600 mt-4 border-t px-4 py-3">
        <x-shop::currency-selector mobile />
      </div>

      <!-- Language Selector - Mobile -->
      <div class="border-surface-600 mt-4 border-t px-4 py-3">
        <x-shop::language-selector mobile />
      </div>
    </nav>

    <div class="border-surface-600 absolute bottom-0 left-0 right-0 border-t p-4">
      <a href="@auth('customer') {{ route('shop.customers.account.profile.index') }}  @else {{ route('shop.customer.session.create') }} @endauth"
        class="hover:text-primary hover:bg-surface-600/50 flex items-center justify-between rounded-lg px-4 py-3 transition-colors"
        @click="showMobileMenu = false">
        <span class="flex items-center">
          <x-lucide-user class="mr-3 h-5 w-5" />
          @auth('customer')
            @lang('shop::app.components.layouts.header.profile')
          @else
            @lang('shop::app.components.layouts.header.sign-in')
          @endauth
        </span>
        <x-lucide-chevron-right class="h-5 w-5" />
      </a>
    </div>
  </div>
</div>
