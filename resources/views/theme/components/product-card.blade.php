@props(['product'])

@php
  $url = url($product->url_key);
  $previewImage = $product->base_image_url;
@endphp

<div class="group">
  <div class="relative overflow-hidden">
    <img src="{{ $previewImage }}" alt="{{ $product->name }}" class="aspect-[3/4] w-full object-cover">
    <button
      class="absolute bottom-4 left-1/2 flex -translate-x-1/2 items-center space-x-2 rounded-full bg-white px-4 py-2 text-black opacity-0 transition-opacity duration-300 group-hover:opacity-100">
      @svg('heroicon-o-shopping-cart', ['class' => 'h-4 w-4'])
      <span>Add to Cart</span>
    </button>
  </div>
  <div class="mt-4">
    <a href="{{ $url }}" class="block">
      <h3 class="text-md line-clamp-2 font-medium">
        {{ $product->name }}
      </h3>
      <p class="text-gray-600">
        {!! $product->getTypeInstance()->getPriceHtml() !!}
      </p>
    </a>
  </div>
</div>
