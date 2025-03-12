@if ($block->type === 'text')
  <div class="prose max-w-none">
    {!! $block->settings->text !!}
  </div>
@elseif ($block->type === 'separator')
  <hr>
@elseif ($block->type === 'title')
  <h1 class="text-secondary font-serif text-3xl">
    {{ $product->name }}
  </h1>
@elseif ($block->type === 'price')
  <x-shop::product.prices :product="$product" />
@elseif($block->type === 'rating' && $totalReviews > 0)
  <div class="flex items-center space-x-4">
    <x-shop::star-rating :rating="$averageRating" />
    <span class="text-secondary text-sm">({{ $totalReviews }})</span>
  </div>
@elseif($block->type === 'short-description')
  <div class="prose">
    {!! visual_clear_inline_styles($product->short_description) !!}
  </div>
@elseif($block->type === 'quantity-selector' && $showQuantitySelector)
  <x-shop::quantity-selector x-on:change="$wire.quantity = $event.detail" />
@elseif($block->type === 'buy-buttons')
  <x-shop::product.buy-buttons :product="$product" :showBuyNowButton="$block->settings->enable_buy_now" />
@elseif($block->type === 'description')
  <div class="prose max-w-none">
    {!! visual_clear_inline_styles($product->description) !!}
  </div>
@elseif($block->type === 'variant-picker' && $hasVariants)
  <x-visual::product-variant-picker :product="$product" />
@elseif($block->type === 'grouped-options' && $product->type === 'grouped')
  <x-shop::product.grouped-options :product="$product" />
@elseif($block->type === 'bundle-options' && $product->type === 'bundle')
  <x-shop::product.bundle-options :product="$product" />
@elseif($block->type === 'downloadable-options' && $product->type === 'downloadable')
  <x-shop::product.downloadable-options :product="$product" />
@endif
