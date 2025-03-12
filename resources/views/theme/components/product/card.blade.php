@props(['product'])

@inject('reviewHelper', \Webkul\Product\Helpers\Review::class)

@php
  $productResource = (new \Webkul\Shop\Http\Resources\ProductResource($product))->toArray(request());
  $url = url($product->url_key);
  $baseImage = product_image()->getProductBaseImage($product);
  $previewImage = $baseImage['medium_image_url'];

  $totalReviews = $reviewHelper->getTotalReviews($product);
  $averageRating = $reviewHelper->getAverageRating($product);
@endphp

<div
  x-data="{ mode: 'grid', isMobile: window.innerWidth < 640, }"
  x-modelable="mode"
  {{ $attributes }}
>
  <div x-show="mode === 'grid' || isMobile"
    class="group relative h-full rounded-lg bg-white shadow-sm transition-shadow hover:shadow-md"
  >
    <div class="relative aspect-square overflow-hidden rounded-t-lg">
      <img
        src="{{ $productResource['base_image']['medium_image_url'] }}"
        alt="{{ $productResource['name'] }}"
        class="h-full w-full object-cover object-center transition-transform duration-300 group-hover:scale-105"
      >

      <div class="absolute inset-0 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
        <a
          class="absolute inset-0"
          href="{{ url($productResource['url_key']) }}"
          aria-label="{{ $productResource['name'] }}"
        ></a>
        <div
          class="absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 transform items-center justify-center gap-2"
        >
          <livewire:add-to-cart-button
            :product-id="$productResource['id']"
            :key="str()->random(16)"
            size="lg"
            rounded
            icon-only
          />

          @auth('customer')
            @if (core()->getConfigData('customer.settings.wishlist.wishlist_option'))
              <livewire:add-to-wishlist-button
                :product-id="$productResource['id']"
                :in-user-wishlist="$productResource['is_wishlist']"
                :key="str()->random(16)"
                size="lg"
              />
            @endif
          @endauth

          @if (core()->getConfigData('catalog.products.settings.compare_option'))
            <livewire:add-to-compare-button :product-id="$productResource['id']" size="lg" />
          @endif
        </div>
      </div>
    </div>

    <div class="p-4">
      <div class="mb-2 flex items-center gap-2">
        @if ($productResource['reviews']['total'] > 0)
          <x-shop::star-rating :rating="$productResource['ratings']['average']" />
          <span class="text-secondary text-sm">({{ $productResource['reviews']['total'] }})</span>
        @endif
      </div>

      <a class="mb-1 line-clamp-2 text-base font-medium text-neutral-700 transition-colors"
        href="{{ url($productResource['url_key']) }}"
      >
        {{ $productResource['name'] }}
      </a>
      <div class="flex items-center justify-between">
        <div
          class="text-primary [&>div>p:nth-of-type(2)]:text-neutral flex items-center gap-2 text-lg font-medium [&_.line-through]:text-neutral-400"
        >
          {!! $productResource['price_html'] !!}
        </div>

        @if ($productResource['on_sale'])
          <span class="bg-danger text-danger-100 rounded-full px-2 py-1 text-xs">
            @lang('shop::app.components.products.card.sale')
          </span>
        @elseif($productResource['is_new'])
          <span class="bg-primary/10 text-primary rounded-full px-2 py-1 text-xs">
            @lang('shop::app.components.products.card.new')
          </span>
        @endif
      </div>
    </div>
  </div>

  <!-- View as list: only on desktop -->
  <div x-show="mode === 'list'"
    class="bg-surface group relative hidden w-full rounded-lg shadow-sm transition-shadow hover:shadow-md sm:flex"
  >
    <div class="relative w-48 flex-shrink-0">
      <img
        src="{{ $productResource['base_image']['medium_image_url'] }}"
        alt="{{ $productResource['name'] }}"
        class="h-full w-full rounded-l-lg object-cover object-center"
      >
    </div>

    <div class="flex flex-1 flex-col justify-between p-6">
      <div>
        <div class="mb-2 flex items-center justify-between">
          <a class="text-xl font-medium text-neutral-700 transition-colors"
            href="{{ url($productResource['url_key']) }}"
          >
            {{ $productResource['name'] }}
          </a>

          @if ($productResource['on_sale'])
            <span class="bg-danger text-danger-100 rounded-full px-2 py-1 text-xs">
              @lang('shop::app.components.products.card.sale')
            </span>
          @elseif($productResource['is_new'])
            <span class="bg-primary/10 text-primary rounded-full px-2 py-1 text-xs">
              @lang('shop::app.components.products.card.new')
            </span>
          @endif
        </div>

        <div class="mb-4 flex items-center gap-2">
          @if ($productResource['reviews']['total'] > 0)
            <x-shop::star-rating :rating="$productResource['ratings']['average']" />
            <span class="text-secondary text-sm">({{ $productResource['reviews']['total'] }})</span>
          @endif
        </div>

        <div class="mb-4 line-clamp-2">
          {!! visual_clear_inline_styles($product->short_description) !!}
        </div>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          @auth('customer')
            @if (core()->getConfigData('customer.settings.wishlist.wishlist_option'))
              <livewire:add-to-wishlist-button
                :product-id="$productResource['id']"
                :in-user-wishlist="$productResource['is_wishlist']"
                :key="str()->random(16)"
              />
            @endif
          @endauth

          @if (core()->getConfigData('catalog.products.settings.compare_option'))
            <livewire:add-to-compare-button :product-id="$productResource['id']" />
          @endif
        </div>

        <div class="flex items-center gap-4">
          <div
            class="text-primary [&>div>p:nth-of-type(2)]:text-neutral flex items-center gap-2 text-lg font-medium [&>div]:flex [&_.line-through]:text-neutral-400"
          >
            {!! $productResource['price_html'] !!}
          </div>

          <livewire:add-to-cart-button
            x-data="{ submit() { this.$wire.addToCart() } }"
            :productId="$productResource['id']"
            :key="str()->random(16)"
          />
        </div>
      </div>
    </div>
  </div>
</div>
