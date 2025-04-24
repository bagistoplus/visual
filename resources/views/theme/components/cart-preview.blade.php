<div
  class="relative"
  x-data
  x-dropdown="{ open: false }"
>
  <!-- Cart Button -->
  <button
    x-dropdown:trigger
    class="relative p-2"
    aria-label="cart preview"
  >
    <x-lucide-shopping-cart class="hover:text-primary h-5 w-5 transition-colors" />
    @if (!$this->isCartEmpty())
      <span class="bg-primary text-primary-100 absolute right-0 top-0 flex h-4 w-4 items-center justify-center rounded-full text-xs">
        {{ $this->getItemsCount() }}
      </span>
    @endif
  </button>

  <!-- Cart Preview -->
  <div
    x-cloak
    x-dropdown:content
    class="bg-surface border-surface-600 absolute right-0 mt-2 w-80 rounded-lg border py-4 shadow-lg"
  >
    <!-- Header -->
    <div class="px-4">
      <h3 class="text-on-surface mb-1 text-xl font-medium">
        @lang('shop::app.checkout.cart.mini-cart.shopping-cart')
      </h3>
      <p class="mb-4 text-sm">
        {{ core()->getConfigData('sales.checkout.mini_cart.offer_info') }}
      </p>
    </div>

    @if (!$this->isCartEmpty())
      <!-- Cart Items -->
      <div class="max-h-64 divide-y overflow-y-auto">
        @foreach ($this->getCartItems() as $item)
          <div class="hover:bg-surface-600/70 not-l px-4 py-2">
            <div class="flex items-start gap-3">
              <!-- Item Image -->
              <div class="flex-shrink-0 overflow-hidden rounded">
                <a class="block h-12 w-12" href="{{ url($item->product_url_key) }}">
                  <img
                    src="{{ $item->base_image->small_image_url }}"
                    alt="{{ $item->name }}"
                    class="h-full w-full object-cover"
                  />
                </a>
              </div>

              <!-- Item Details -->
              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between">
                  <h4 class="text-on-surface truncate text-sm font-medium">
                    <a href="{{ url($item->product_url_key) }}">
                      {{ $item->name }}
                    </a>
                  </h4>
                  <button class="hover:text-danger hover:bg-danger/10 rounded-full p-1 transition-colors" x-on:click="$confirm(() => $wire.removeItem({{ $item->id }}))">
                    <x-lucide-trash-2 class="h-4 w-4" />
                  </button>
                </div>
                <div class="mt-1 flex items-start justify-between">
                  <!-- Quantity Controls -->
                  <div class="flex items-center gap-2 rounded border border-neutral-200">
                    <button class="hover:bg-surface-alt rounded-full p-1 transition-colors" wire:click="updateItemQuantity({{ $item->id }}, {{ $item->quantity - 1 }})">
                      <x-lucide-minus class="h-3 w-3" />
                    </button>

                    <span class="text-secondary min-w-[20px] text-center text-xs">
                      {{ $item->quantity }}
                    </span>

                    <button class="hover:bg-surface-alt rounded-full p-1 transition-colors" wire:click="updateItemQuantity({{ $item->id }}, {{ $item->quantity + 1 }})">
                      <x-lucide-plus class="h-3 w-3" />
                    </button>
                  </div>

                  <!-- Item Price -->
                  <div class="text-right">
                    @if ($this->shouldDisplayCartPricesIncludingTax())
                      <div class="text-primary text-sm font-medium">
                        <x-shop::formatted-price :price="$item->price_incl_tax" />
                      </div>
                    @elseif ($this->shouldDisplayCartBothPrices())
                      <div class="text-primary text-xs font-medium">
                        <x-shop::formatted-price :price="$item->price_incl_tax" />
                      </div>
                      <div class="text-secondary text-[0.6rem]">
                        @lang('shop::app.checkout.cart.mini-cart.excl-tax')
                        <x-shop::formatted-price :price="$item->price" />
                      </div>
                    @else
                      <div class="text-secondary text-xs">
                        <x-shop::formatted-price :price="$item->price" />
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <!-- Subtotal and Actions -->
      <div class="border-surface-600 mt-3 border-t px-4 pt-3">
        <!-- Subtotal -->
        <div class="mb-3 flex justify-between">
          <span class="text-on-surface font-medium">
            @lang('shop::app.checkout.cart.mini-cart.subtotal')
          </span>
          @if ($this->shouldDisplayCartSubtotalIncludingTax())
            <span class="font-bold text-neutral-900">
              {{ $this->getFormattedCartSubtotalWithTax() }}
            </span>
          @elseif ($this->shouldDisplayCartBothSubtotals())
            <div class="flex flex-col justify-end text-right">
              <span class="font-bold text-neutral-900">
                {{ $this->getFormattedCartSubtotalWithTax() }}
              </span>
              <span class="text-xs">
                @lang('shop::app.checkout.cart.mini-cart.excl-tax')
                {{ $this->getFormattedCartSubtotal() }}
              </span>
            </div>
          @else
            <span class="font-bold text-neutral-900">
              {{ $this->getFormattedCartSubtotal() }}
            </span>
          @endif
        </div>

        <!-- Action Buttons -->
        <a href="{{ route('shop.checkout.onepage.index') }}" class="bg-primary text-primary-100 mt-2 block w-full rounded-full py-2 text-center">
          @lang('shop::app.checkout.cart.mini-cart.continue-to-checkout')
        </a>
        <a href="{{ route('shop.checkout.cart.index') }}" class="mt-4 block w-full cursor-pointer text-center text-sm font-medium hover:underline">
          @lang('shop::app.checkout.cart.mini-cart.view-cart')
        </a>
      </div>
    @else
      <!-- Empty Cart Message -->
      <div class="px-4 py-8 text-center">
        <p class="text-secondary mb-4">
          @lang('shop::app.checkout.cart.mini-cart.empty-cart')
        </p>
        <a href="/shop" class="bg-primary text-primary-100 inline-block rounded-full px-4 py-2">
          Start Shopping
        </a>
      </div>
    @endif
  </div>
</div>
