@props([
    'showBuyNowButton' => false,
])

<div class="flex w-full max-w-sm flex-col gap-4">
  <x-shop::add-to-cart-button
    action="addToCart"
    variant="{{ $showBuyNowButton ? 'primary-outline' : 'primary' }}"
    class="w-full"
  />

  @if ($showBuyNowButton)
    <x-shop::add-to-cart-button
      action="buyNow"
      variant="primary"
      class="w-full"
      text="{{ __('Buy now') }}"
    />
  @endif
</div>
