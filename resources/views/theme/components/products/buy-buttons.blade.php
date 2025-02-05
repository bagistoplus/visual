@props([
    'showBuyNowButton' => false,
])

@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('VisualBuyButtons', () => ({
        disableButtons: false,

        init() {
          window.addEventListener('product-variant-change', (event) => {
            this.disableButtons = !event.detail.variant;
          });
        }
      }));
    });
  </script>
@endpushOnce

<div x-data="VisualBuyButtons" class="flex w-full max-w-sm flex-col gap-4">
  <x-shop::add-to-cart-button
    action="addToCart"
    variant="{{ $showBuyNowButton ? 'primary-outline' : 'primary' }}"
    class="w-full"
    x-bind:disabled="disableButtons"
  />

  @if ($showBuyNowButton)
    <x-shop::add-to-cart-button
      action="buyNow"
      variant="primary"
      class="w-full"
      text="{{ __('Buy now') }}"
      x-bind:disabled="disableButtons"
    />
  @endif
</div>
