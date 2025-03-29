<div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
  @if ($this->isCartEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-center">
      <p>@lang('shop::app.checkout.cart.index.empty-product')</p>

      <p class="py-10">
        <a href="{{ route('shop.home.index') }}" class="bg-primary text-primary-50 rounded-lg px-6 py-4 hover:opacity-90">
          {{ __('shop::app.checkout.cart.index.continue-shopping') }}
        </a>
      </p>
    </div>
  @else
    <div class="grid grid-cols-1 gap-12 lg:grid-cols-3" x-data="{
        allSelected: false,
        selected: $wire.entangle('itemsSelected'),
        items: @json(collect($cart->items)->pluck('id')),
        toggleAll() {
            if (!this.allSelected) {
                this.selected = this.items;
                this.allSelected = true;
            } else {
                this.selected = [];
                this.allSelected = false;
            }
        }
    }">
      <div class="space-y-4 lg:col-span-2">
        <div class="flex justify-between border-b pb-2">
          <div class="flex items-center gap-4">
            <input
              type="checkbox"
              x-model="allSelected"
              x-on:click="toggleAll"
            >
            <span x-text="'@lang('shop::app.checkout.cart.index.items-selected')'.replace(':count', selected.length)">
            </span>
          </div>

          <button
            x-show="selected.length > 0"
            class="text-primary"
            wire:click="removeSelectedItems"
          >
            @lang('shop::app.checkout.cart.index.remove')
          </button>
        </div>

        @foreach ($cart->items as $item)
          <div class="flex gap-6 border-b border-gray-200 pb-4">
            <input
              type="checkbox"
              value="{{ $item->id }}"
              class="mt-8"
              wire:model.number="itemsSelected"
              x-model.number="selected"
              @change="allSelected = (selected.length === items.length)"
            >
            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg">
              <img
                src="{{ $item->base_image->small_image_url }}"
                alt="{{ $item->name }}"
                class="h-full w-full object-cover"
              >
            </div>

            <div class="flex-1">
              <div class="flex justify-between">
                <div>
                  <h3 class="truncate text-lg font-medium text-neutral-700">
                    <a href="{{ url($item->product_url_key) }}">
                      {{ $item->name }}
                    </a>
                  </h3>

                  <!-- Item Price -->
                  @if ($this->shouldDisplayCartPricesIncludingTax())
                    <div class="text-primary font-medium">
                      <x-shop::formatted-price :price="$item->price_incl_tax" />
                    </div>
                  @elseif ($this->shouldDisplayCartBothPrices())
                    <div class="text-primary font-medium">
                      <x-shop::formatted-price :price="$item->price_incl_tax" />
                    </div>
                    <div class="text-secondary text-[0.6rem]">
                      @lang('shop::app.checkout.cart.mini-cart.excl-tax')
                      <x-shop::formatted-price :price="$item->price" />
                    </div>
                  @else
                    <div class="text-secondary">
                      <x-shop::formatted-price :price="$item->price" />
                    </div>
                  @endif
                </div>
                <button
                  class="text-neutral-400 hover:text-neutral-500"
                  title="Remove item"
                  x-on:click="$confirm(() => $wire.removeItem({{ $item->id }}))"
                >
                  <x-lucide-trash-2 class="h-5 w-5" />
                </button>
              </div>
              <div class="mt-2 flex items-center">
                <x-shop::quantity-selector
                  label=""
                  value="{{ $item->quantity }}"
                  x-on:change="$wire.updateItemQuantity({{ $item->id }}, $event.detail)"
                />
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="lg:col-span-1">
        <div class="bg-surface-alt rounded-lg p-6">
          <h2 class="mb-4 text-lg font-medium text-neutral-700">
            @lang('shop::app.checkout.cart.summary.cart-summary')
          </h2>

          <!-- Estimate Tax and Shipping -->
          @if (core()->getConfigData('sales.checkout.shopping_cart.estimate_shipping') && $haveStockableItems)
            <livewire:estimate-shipping class="mb-6" />
          @endif

          <x-shop::cart.summary :cart="$cart" />

          <a href="{{ route('shop.checkout.onepage.index') }}" class="bg-primary hover:bg-primary/90 text-primary-50 mt-6 block w-full rounded-full px-6 py-3 text-center transition-colors">
            @lang('shop::app.checkout.cart.summary.proceed-to-checkout')
          </a>
        </div>
      </div>
    </div>
  @endif
</div>
