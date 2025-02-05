@props([
    'showBuyNowButton' => false,
])

@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.store('ProductForm', {
        validators: [],
        disableButtons: false,


        register(fn) {
          this.validators.push(fn);
        },

        validate() {
          return this.validators.every(fn => fn());
        }
      });

      Alpine.data('VisualBuyButtons', () => ({
        get disableButtons() {
          return Alpine.store('ProductForm').disableButtons;
        },

        submit(action) {
          if (this.disableButtons) return;

          if (!Alpine.store('ProductForm').validate()) return;

          this.$wire.call(action);
        },
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
