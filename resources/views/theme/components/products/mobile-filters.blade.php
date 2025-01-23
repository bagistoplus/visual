@props(['maxPrice', 'sortOptions', 'paginationLimits'])

<div x-cloak x-show="showMobileFilters" class="fixed inset-0 z-50 lg:hidden" x-cloak>
  <div class="absolute inset-0 bg-black/50" @click="showMobileFilters = false"></div>
  <div
    class="absolute inset-y-0 right-0 flex h-screen w-full max-w-xs transform flex-col bg-white transition-transform duration-300">
    <div class="flex flex-none items-center justify-between border-b border-gray-200 p-4">
      <h3 class="text-secondary text-lg font-medium">Sort & Filter</h3>
      <button class="text-secondary hover:text-primary rounded-lg p-2" @click="showMobileFilters = false">
        <x-lucide-x class="h-6 w-6" />
      </button>
    </div>

    <div class="flex-1 space-y-6 overflow-y-auto p-4">
      <x-shop::products.filters no-header :maxPrice="$maxPrice" />

      @if ($sortOptions->isNotEmpty())
        <label class="block">
          <span class="text-neutral-700">Sort by</span>
          <select class="mt-1 w-full" name="sort" wire:model.live="sort">
            @foreach ($sortOptions as $option)
              <option value="{{ $option['value'] }}">
                {{ $option['title'] }}
              </option>
            @endforeach
          </select>
        </label>
      @endif

      @if ($paginationLimits->isNotEmpty())
        <label class="block">
          <span class="text-neutral-700">Items per page</span>
          <select class="mt-1 w-full" name="sort" wire:model.live="limit">
            @foreach ($paginationLimits as $limit)
              <option value="{{ $limit }}">
                {{ $limit }}
              </option>
            @endforeach
          </select>
        </label>
      @endif
    </div>

    <div class="flex-none border-t border-gray-200 p-4">
      <button x-on:click="showMobileFilters = false" wire:click="resetFilters"
        class="bg-primary flex w-full items-center justify-center rounded-lg px-4 py-2 text-white hover:opacity-90">
        <x-lucide-rotate-cw class="mr-2 h-4 w-4" />
        Reset All Filters
      </button>
    </div>
  </div>
</div>
