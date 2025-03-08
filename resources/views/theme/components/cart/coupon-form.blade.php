<form class="space-y-2" wire:submit.prevent="applyCoupon">
  <label for="coupon" class="block text-sm font-medium text-gray-700">
    @lang('shop::app.checkout.coupon.apply')
  </label>

  @if ($cart['coupon_code'])
    <div class="bg-success-50 flex items-center justify-between rounded-lg p-3">
      <div>
        <span class="text-success text-sm font-medium">
          @lang('shop::app.checkout.coupon.applied'): {{ $cart['coupon_code'] }}
        </span>
      </div>
      <button
        type="button"
        class="text-success hover:opacity-80"
        wire:click="removeCoupon"
      >
        <x-lucide-trash-2 class="h-5 w-5" />
      </button>
    </div>
  @else
    <div class="flex items-start gap-2">
      <div class="flex-1">
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
            <x-lucide-ticket class="h-5 w-5 text-neutral-400" />
          </div>
          <input
            id="coupon"
            type="text"
            placeholder="Enter code"
            class="focus:ring-primary focus:border-primary w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 focus:ring-2"
            wire:model="couponCode"
          >
        </div>
        @error('code')
          <p class="text-danger text-xs italic">{{ $message }}</p>
        @enderror
      </div>

      <button
        type="submit"
        wire:loading.attr="disabled"
        class="bg-primary relative rounded-lg px-4 py-2 text-white transition-opacity hover:opacity-90"
      >
        <span wire:loading.class="text-transparent">
          @lang('shop::app.checkout.coupon.button-title')
        </span>

        <div wire:loading.flex class="absolute inset-0 h-full w-full items-center justify-center">
          <x-lucide-loader-2 class="h-5 w-5 animate-spin" />
        </div>

      </button>
    </div>
  @endif
</form>
