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

<div class="border-surface-600 flex h-16 items-center justify-between border-b px-4">
  <h2 class="text-lg font-medium">Menu</h2>
  <button
    class="hover:text-primary p-2 transition-colors"
    aria-label="Close menu"
    x-on:click="open = false"
  >
    <x-heroicon-o-x-mark class="h-6 w-6" />
  </button>
</div>

<ul class="mt-2 divide-y" role="navigation">
  @foreach ($categories as $category)
    <li x-data="{ subMenuOpen: false }">
      @if ($category->children->isEmpty())
        <a href="{{ $category->url }}" class="block rounded px-3 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-100">
          {{ $category->name }}
        </a>
      @else
        <button
          type="button"
          class="flex w-full items-center justify-between rounded px-3 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-100"
          x-on:click="subMenuOpen = !subMenuOpen"
        >
          <span>{{ $category->name }}</span>
          <x-lucide-chevron-right class="h-4 w-4 transform transition-transform" x-bind:class="{ 'rotate-90': subMenuOpen }" />
        </button>
        <div
          x-show="subMenuOpen"
          x-transition
          class="space-y-2 p-4"
        >
          @foreach ($category->children as $subCategory)
            <a href="{{ $subCategory->url }}" class="block text-sm text-neutral-700 hover:underline">
              {{ $subCategory->name }}
            </a>
          @endforeach
        </div>
      @endif
    </li>
  @endforeach
</ul>

<!-- Currency Selector - Mobile -->
<div class="border-surface-600 mt-4 border-t px-4 py-3">
  <x-shop::currency-selector mobile />
</div>

<!-- Language Selector - Mobile -->
<div class="border-surface-600 mt-4 border-t px-4 py-3">
  <x-shop::language-selector mobile />
</div>

<div class="border-surface-600 absolute bottom-0 left-0 right-0 border-t p-4">
  <a
    href="@auth('customer') {{ route('shop.customers.account.profile.index') }}  @else {{ route('shop.customer.session.create') }} @endauth"
    class="hover:text-primary hover:bg-surface-600/50 flex items-center justify-between rounded-lg px-4 py-3 transition-colors"
    @click="showMobileMenu = false"
  >
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
