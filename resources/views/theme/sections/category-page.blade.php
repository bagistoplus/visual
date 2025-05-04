<div x-data="{ displayMode: $wire.entangle('displayMode') }">
  <div class="bg-surface-alt relative py-8">
    @if ($category->banner_url)
      <img
        src="{{ $category->banner_url }}"
        alt="{{ $category->name }}"
        class="absolute inset-0 h-full w-full object-cover object-center"
      >
    @endif
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative mx-auto max-w-7xl px-4 text-neutral-200 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-semibold">
        {{ $category->name }}
      </h1>
      <div class="mt-2">
        {!! visual_clear_inline_styles($category->description) !!}
      </div>
    </div>
  </div>

  <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-8 md:flex-row">
      <x-shop::product.filters class="hidden w-64 md:block" :maxPrice="$maxPrice" />

      <x-shop::product.mobile-filters
        :maxPrice="$maxPrice"
        :sortOptions="$this->availableSortOptions"
        :paginationLimits="$this->availablePaginationLimits"
      />

      <div class="flex-1">
        <!-- Toolbar -->
        <x-shop::product.toolbar :availableSortOptions="$this->availableSortOptions" :availablePaginationLimits="$this->availablePaginationLimits" />

        <!-- Products grid view -->
        <div x-show="displayMode === 'grid'" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          @foreach ($products as $product)
            <x-shop::product.card
              :key="$product->id"
              :product="$product"
              x-model="displayMode"
            />
          @endforeach
        </div>

        <!-- Products list view -->
        <div x-show="displayMode === 'list'" class="grid grid-cols-1 gap-6">
          @foreach ($products as $product)
            <x-shop::product.card
              :key="$product->id"
              :product="$product"
              x-model="displayMode"
            />
          @endforeach
        </div>

        <div class="mt-4">
          {{ $products->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
