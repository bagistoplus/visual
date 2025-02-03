@php
  $groupedProducts = $product->grouped_products()->orderBy('sort_order')->get();
@endphp

@if ($groupedProducts->count())
  <div class="grid w-full max-w-sm gap-4">
    @foreach ($groupedProducts as $groupedProduct)
      @if ($groupedProduct->associated_product->getTypeInstance()->isSaleable())
        <div class="flex items-center justify-between gap-4">
          <div class="text-xs">
            <p class="font-semibold text-neutral-600">
              @lang('shop::app.products.view.type.grouped.name')
            </p>
            <p class="mt-1 line-clamp-1">
              <span>{{ $groupedProduct->associated_product->name }} + </span>
              <x-shop::formatted-price :price="$groupedProduct->associated_product->getTypeInstance()->getFinalPrice()" />
            </p>
          </div>
          <x-shop::quantity-selector
            label=''
            value="{{ $groupedProduct->qty }}"
            x-on:change="data.qty[{{ $groupedProduct->associated_product_id }}] = $event.detail"
          />
        </div>
      @endif
    @endforeach
  </div>
@endif
