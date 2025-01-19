@props(['product'])

@inject('reviewHelper', \Webkul\Product\Helpers\Review::class)

@php
  $url = url($product->url_key);
  $previewImage = $product->base_image_url;

  $totalReviews = $reviewHelper->getTotalReviews($product);
  $averageRating = $reviewHelper->getAverageRating($product);
@endphp

<div class="group relative rounded-lg bg-white shadow-sm transition-shadow hover:shadow-md">
  <div class="relative aspect-square overflow-hidden rounded-t-lg">
    <img src="{{ $previewImage }}" alt="{{ $product->name }}"
      class="w-full object-cover object-center transition-transform duration-300 group-hover:scale-105">

    <div class="absolute inset-0 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
      <a class="absolute inset-0" href="{{ url($product->url_key) }}"></a>
      <div
        class="absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 transform items-center justify-center gap-2">
        <livewire:add-to-cart-button :productId="$product->id" simple />
        <button class="text-secondary hover:text-primary rounded-full bg-white p-3 transition-colors">
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

    <a class="mb-1 line-clamp-2 block text-base font-medium text-neutral-700 transition-colors"
      href="{{ url($product->url_key) }}">
      {{ $product->name }}
    </a>
    <div class="flex items-center justify-between">
      <div class="text-primary text-lg font-medium">
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
