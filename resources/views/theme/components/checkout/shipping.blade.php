@props(['cart', 'shippingMethods' => []])
<div>
  <h2 class="text-base font-semibold text-neutral-700 md:text-2xl">
    @lang('shop::app.checkout.onepage.shipping.shipping-method')
  </h2>

  <div class="mt-4 space-y-4">
    @foreach ($shippingMethods as $shippingMethod)
      @foreach ($shippingMethod['rates'] as $rate)
        <label
          class="has-[:checked]:border-primary has-[:checked]:bg-primary-50 block cursor-pointer rounded-lg border-2 border-neutral-200 p-4 transition-colors"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                type="radio"
                name="shippingMethod"
                class="text-primary h-4 w-4 border-gray-300"
                wire:change="handleShippingMethod('{{ $rate['method'] }}')"
              >
              <div class="ml-2">
                <p class="font-medium">{{ $rate['method_title'] }}</p>
                <p class="text-sm">{{ $rate['method_description'] }}</p>
              </div>
            </div>
            <span class="font-medium">
              <x-shop::formatted-price :price="$rate['price']" />
            </span>
          </div>
        </label>
      @endforeach
    @endforeach
  </div>
</div>
