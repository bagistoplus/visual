@props(['categories'])

<ul class="divide-y border-t pt-2" role="navigation">
  @foreach ($categories as $category)
    @if ($category->children->isEmpty())
      <li>
        <a href="{{ $category->url }}" class="block rounded px-3 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-100">
          {{ $category->name }}
        </a>
      </li>
    @else
      <li x-collapsible>
        <button
          type="button"
          class="flex w-full items-center justify-between rounded px-3 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-100"
          x-collapsible:trigger
        >
          <span>{{ $category->name }}</span>
          <x-lucide-chevron-right x-collapsible:indicator class="h-4 w-4 transform transition-transform data-[state=open]:rotate-90" />
        </button>
        <div x-collapsible:content class="space-y-2 p-3">
          @foreach ($category->children as $subCategory)
            <a href="{{ $subCategory->url }}" class="block text-sm text-neutral-700 hover:underline">
              {{ $subCategory->name }}
            </a>
          @endforeach
        </div>
      </li>
    @endif
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
  <a href="@auth('customer') {{ route('shop.customers.account.profile.index') }}  @else {{ route('shop.customer.session.create') }} @endauth"
    class="hover:text-primary hover:bg-surface-600/50 flex items-center justify-between rounded-lg px-4 py-3 transition-colors"
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
