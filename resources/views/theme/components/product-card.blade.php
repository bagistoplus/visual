@props(['product'])

@inject('reviewHelper', \Webkul\Product\Helpers\Review::class)

@php
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
  <span></span>
  <div x-show="mode === 'grid' || isMobile"
    class="group relative h-full rounded-lg bg-white shadow-sm transition-shadow hover:shadow-md"
  >
    <div class="relative aspect-square overflow-hidden rounded-t-lg">
      <img
        src="{{ $previewImage }}"
        alt="{{ $product->name }}"
        class="h-full w-full object-cover object-center transition-transform duration-300 group-hover:scale-105"
      >

      <div class="absolute inset-0 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
        <a
          class="absolute inset-0"
          href="{{ url($product->url_key) }}"
          aria-label="{{ $product->name }}"
        ></a>
        <div
          class="absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 transform items-center justify-center gap-2"
        >
          <livewire:add-to-cart-button
            :productId="$product->id"
            wire:key="{{ str()->random(16) }}"
            simple
          />
          <button aria-label="add to whishlist"
            class="text-secondary hover:text-primary rounded-full bg-white p-3 transition-colors"
          >
            <x-lucide-heart class="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>

    <div class="p-4">
      <div class="mb-2 flex items-center gap-2">
        @if ($totalReviews > 0)
          <x-shop::star-rating :rating="$averageRating" />
          <span class="text-secondary text-sm">({{ $totalReviews }})</span>
        @endif
      </div>

      <a class="mb-1 line-clamp-2 text-base font-medium text-neutral-700 transition-colors"
        href="{{ url($product->url_key) }}"
      >
        {{ $product->name }}
      </a>
      <div class="flex items-center justify-between">
        <div
          class="text-primary [&>div>p:nth-of-type(2)]:text-neutral flex items-center gap-2 text-lg font-medium [&_.line-through]:text-neutral-400"
        >
          {!! $product->getTypeInstance()->getPriceHtml() !!}
        </div>

        @if ($product->getTypeInstance()->haveDiscount())
          <span class="bg-danger text-danger-100 rounded-full px-2 py-1 text-xs">
            @lang('shop::app.components.products.card.sale')
          </span>
        @elseif($product->new)
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
        src="{{ $previewImage }}"
        alt="{{ $product->name }}"
        class="h-full w-full rounded-l-lg object-cover object-center"
      >
    </div>

    <div class="flex flex-1 flex-col justify-between p-6">
      <div>
        <div class="mb-2 flex items-center justify-between">
          <a class="text-xl font-medium text-neutral-700 transition-colors" href="{{ url($product->url_key) }}">
            {{ $product->name }}
          </a>

          @if ($product->getTypeInstance()->haveDiscount())
            <span class="bg-danger text-danger-100 rounded-full px-2 py-1 text-xs">
              @lang('shop::app.components.products.card.sale')
            </span>
          @elseif($product->new)
            <span class="bg-primary/10 text-primary rounded-full px-2 py-1 text-xs">
              @lang('shop::app.components.products.card.new')
            </span>
          @endif
        </div>

        <div class="mb-4 flex items-center gap-2">
          @if ($totalReviews > 0)
            <x-shop::star-rating :rating="$averageRating" />
            <span class="text-secondary text-sm">({{ $totalReviews }})</span>
          @endif
        </div>

        <div class="mb-4 line-clamp-2">
          {!! visual_clear_inline_styles($product->short_description) !!}
        </div>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <!-- quick actions: like, compare -->
        </div>
        <div class="flex items-center gap-4">
          <div class="text-primary flex items-center gap-2 text-xl font-medium [&>div]:flex">
            {!! $product->getTypeInstance()->getPriceHtml() !!}
          </div>

          <livewire:add-to-cart-button :productId="$product->id" wire:key="{{ str()->random(16) }}" />
        </div>
      </div>
    </div>
  </div>
</div>
