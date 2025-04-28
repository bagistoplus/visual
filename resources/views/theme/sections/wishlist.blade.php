<div class="bg-background rounded-lg shadow-sm" x-data>
  <div class="border-b border-neutral-100 p-4">
    <div class="flex items-center justify-between">
      <h1 class="font-serif text-2xl text-neutral-700">
        @lang('shop::app.customers.account.reviews.title')
      </h1>

      @if ($wishlistItems->isNotEmpty())
        <x-shop::ui.button
          color="primary"
          variant="soft"
          icon="lucide-trash"
          size="sm"
          wire:target="removeAll"
          x-on:click="$confirm(() => $wire.removeAll())"
        >
          @lang('shop::app.customers.account.wishlist.delete-all')
        </x-shop::ui.button>
      @endif
    </div>
  </div>
  <div class="space-y-6 p-3 sm:p-6">
    @forelse($wishlistItems as $item)
      <div class="border-b border-neutral-100 pb-4 last:border-b-0" x-data="{ quantity: 1 }">
        <div class="flex gap-6 pb-4">
          <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg">
            <img
              src="{{ $item->product->base_image->small_image_url }}"
              alt="{{ $item->product->name }}"
              class="h-full w-full object-cover"
            >
          </div>

          <div class="flex-1">
            <div class="flex items-start justify-between gap-2">
              <div>
                <h3 class="text-lg font-medium text-neutral-700">
                  <a href="{{ url($item->product->url_key) }}">
                    {{ $item->product->name }}
                  </a>
                </h3>

                <!-- Item Price -->
                <div class="text-primary font-medium">
                  {{ $item->product->min_price }}
                </div>
              </div>

              <button
                class="flex-none text-neutral-400 hover:text-neutral-500 sm:inline"
                title="Remove item"
                x-on:click="$confirm(() => $wire.removeItem({{ $item->id }}))"
              >
                <x-lucide-trash-2 class="h-5 w-5" />
              </button>
            </div>

            <!--Wishlist Item attributes -->
            <div class="flex flex-wrap gap-2">
            </div>

            <div class="mt-2 hidden items-center gap-4 sm:flex">
              <x-shop::quantity-selector :min="1" x-on:change="quantity = $event.detail" />
              @if (core()->getConfigData('sales.checkout.shopping_cart.cart_page'))
                <x-shop::wishlist.move-to-cart-btn :id="$item->id" :product-id="$item->product->id" />
              @endif
            </div>
          </div>
        </div>
        <div class="mt flex items-center gap-4 sm:hidden">
          <x-shop::quantity-selector :min="1" x-on:change="quantity = $event.detail" />
          @if (core()->getConfigData('sales.checkout.shopping_cart.cart_page'))
            <x-shop::wishlist.move-to-cart-btn :id="$item->id" :product-id="$item->product->id" />
          @endif
        </div>
      </div>
    @empty
      <div class="flex flex-col items-center justify-center gap-6 py-8">
        <x-lucide-heart-off class="h-16 w-16" />
        <p class="text-lg">
          @lang('shop::app.customers.account.wishlist.empty')
        </p>
      </div>
    @endforelse
  </div>
</div>
