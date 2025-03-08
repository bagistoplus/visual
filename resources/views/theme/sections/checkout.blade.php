<section>
  <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">

      <div class="space-y-4">
        @if (in_array($currentStep, ['address', 'shipping', 'payment', 'review']))
          <x-shop::checkout.address
            :saved-addresses="$savedAddresses"
            :billing-address="$billingAddress"
            :shipping-address="$shippingAddress"
            :countries="$countries"
            :states="$states"
            :cart-have-stockable-items="$this->cartHaveStockableItems()"
          />
        @endif

        @if (in_array($currentStep, ['shipping', 'payment', 'review']))
          <x-shop::checkout.shipping :cart="$cartResource" :shipping-methods="$shippingMethods" />
        @endif

        @if (in_array($currentStep, ['payment', 'review']))
          <x-shop::checkout.payment :payment-methods="$paymentMethods" />
        @endif

        @if ($currentStep === 'review')
          <x-shop::checkout.buttons :cart="$cartResource" class="lg:hidden" />
        @endif
      </div>

      <div class="order-first lg:order-last">
        <div class="lg:sticky lg:top-8">
          <div class="bg-surface-alt rounded-lg p-6 shadow-sm">
            <h2 class="mb-6 font-serif text-xl text-neutral-700">
              @lang('shop::app.checkout.onepage.summary.cart-summary')
            </h2>
            <div class="mb-6 space-y-4">
              @foreach ($cartResource['items'] as $item)
                <div class="flex gap-4">
                  <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100">
                    <img
                      src="{{ $item['base_image']['small_image_url'] }}"
                      alt="Rose Quartz Face Serum"
                      class="h-full w-full object-cover"
                    >
                  </div>
                  <div class="flex-1">
                    <h3 class="font-medium">{{ $item['name'] }}</h3>
                    <p class="text-sm">Quantity: {{ $item['quantity'] }}</p>
                    <p class="text-primary text-sm">{{ $item['formatted_price'] }}</p>
                  </div>
                </div>
              @endforeach
            </div>

            <x-shop::cart.summary :cart="$cartResource" />

            @if ($currentStep === 'review')
              <x-shop::checkout.buttons :cart="$cartResource" class="mt-4 hidden lg:block" />
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
