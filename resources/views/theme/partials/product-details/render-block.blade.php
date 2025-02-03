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
  <x-shop::products.prices :product="$product" />
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
  <x-shop::quantity-selector x-on:change="quantity = $event.detail" />
@elseif($block->type === 'buy-buttons')
  <div class="flex w-full max-w-sm flex-col gap-4">
    <div class="w-full">
      <livewire:add-to-cart-button
        fullwidth
        x-model="quantity"
        variant="{{ $block->settings->enable_buy_now ? 'primary-outline' : 'primary' }}"
        :productId="$product->id"
        wire:key="{{ str()->random(16) }}"
      />
    </div>
    <div class="w-full">
      @if ($block->settings->enable_buy_now)
        <livewire:add-to-cart-button
          x-model="quantity"
          fullwidth
          text="Buy now"
          action="buyNow"
          :productId="$product->id"
          wire:key="{{ str()->random(16) }}"
        />
      @endif
    </div>
  </div>
@elseif($block->type === 'description')
  <div class="prose max-w-none">
    {!! visual_clear_inline_styles($product->description) !!}
  </div>
@elseif($block->type === 'variant-picker' && $hasVariants)
  <x-visual::product-variant-picker :product="$product" />
@elseif($block->type === 'grouped-options' && $product->type === 'grouped')
  <x-shop::products.grouped-options :product="$product" />
@elseif($block->type === 'bundle-options' && $product->type === 'bundle')
  <x-shop::products.bundle-options :product="$product" />
@elseif($block->type === 'downloadable-options' && $product->type === 'downloadable')
  <x-shop::products.downloadable-options :product="$product" />
@endif
