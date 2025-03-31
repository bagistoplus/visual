@props([
    'savedAddresses' => [],
    'billingAddress' => [],
    'shippingAddress' => [],
    'cartHaveStockableItems' => false,
    'countries' => [],
    'states' => [],
])

<div>
  <h2 class="text-base font-semibold text-neutral-700 md:text-2xl">
    @lang('shop::app.checkout.onepage.address.title')
  </h2>

  <form
    class="mt-2"
    wire:submit.prevent="handleAddressForm"
    x-data="{ useBillingAddressForShipping: @js($billingAddress['use_for_shipping']) }"
  >
    @csrf
    <x-shop::checkout.address-fields
      name="billing"
      :saved-addresses="$savedAddresses"
      :address="$billingAddress"
      :countries="$countries"
      :states="$states"
      :show-use-for-shipping-checkbox="$cartHaveStockableItems"
      title="{{ trans('shop::app.checkout.onepage.address.billing-address') }}"
    />

    <div x-show="!useBillingAddressForShipping">
      <hr class="my-4">
      <x-shop::checkout.address-fields
        name="shipping"
        :saved-addresses="$savedAddresses"
        :address="$shippingAddress"
        :countries="$countries"
        :states="$states"
        title="{{ trans('shop::app.checkout.onepage.address.shipping-address') }}"
      />
    </div>

    <div class="mt-4 text-right">
      <button
        type="submit"
        wire:loading.attr="disabled"
        wire:target="handleAddressForm"
        class="bg-primary text-primary-50 ring-primary relative rounded-lg px-6 py-2 ring-offset-2 hover:opacity-90 focus:ring-2"
      >
        <span wire:target="handleAddressForm" wire:loading.class="text-transparent">
          {{ trans('shop::app.checkout.onepage.address.proceed') }}
        </span>

        <div
          wire:loading
          wire:target="handleAddressForm"
          wire:loading.class="!flex"
          class="absolute inset-0 h-full w-full items-center justify-center"
        >
          <x-lucide-loader-2 class="h-5 w-5 animate-spin" />
        </div>
      </button>
    </div>
  </form>
</div>
