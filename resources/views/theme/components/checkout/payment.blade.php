@props([
    'paymentMethods' => [],
])

<div id="payment">
  <h2 class="text-base font-semibold text-neutral-700 md:text-2xl">
    @lang('shop::app.checkout.onepage.payment.payment-method')
  </h2>
  <div class="mt-4 space-y-4">
    @foreach ($paymentMethods as $paymentMethod)
      <label class="has-[:checked]:border-primary has-[:checked]:bg-primary-50 block cursor-pointer rounded-lg border-2 border-gray-200 p-4 transition-colors hover:border-gray-300">
        <div class="flex items-center">
          <input
            type="radio"
            name="paymentMethod"
            value="{{ $paymentMethod['method'] }}"
            wire:model="selectedPaymentMethod"
            wire:change="handlePaymentMethod('{{ $paymentMethod['method'] }}')"
          />
          <div class="ml-2 flex w-full items-center justify-between">
            <div>
              <p class="font-medium">{{ $paymentMethod['method_title'] }}</p>
              <p class="text-sm">{{ $paymentMethod['description'] }}</p>
            </div>

            <img
              class="h-auto w-12"
              src="{{ $paymentMethod['image'] }}"
              alt="{{ $paymentMethod['method_title'] }}"
              title="{{ $paymentMethod['method_title'] }}"
            />
          </div>
        </div>
      </label>
    @endforeach
  </div>
</div>
