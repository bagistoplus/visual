@php
  $products = $getProducts();
@endphp

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
  <div class="text-center">
    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">Featured Products</h2>
    <p class="mt-4 text-base text-gray-500">Handpicked favorites just for you</p>
  </div>
  <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
    @forelse($products as $product)
      <x-shop::product-card :product="$product" />
    @empty
    @endforelse
  </div>
</section>
