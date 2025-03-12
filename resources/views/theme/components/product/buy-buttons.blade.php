@props(['product' => null, 'showBuyNowButton' => false])

@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.store('ProductForm', {
        validators: [],

        register(fn) {
          this.validators.push(fn);
        },

        validate() {
          return this.validators.every(fn => fn());
        }
      });

      Alpine.data('VisualBuyButtons', () => ({
        init() {
          console.log(this.$wire.test)
        },

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
  <x-shop::ui.button
    wire:loading.attr="disabled"
    wire:target="addToCart"
    icon="lucide-shopping-cart"
    :disabled="!$product->isSaleable(1)"
    variant="{{ $showBuyNowButton ? 'outline' : 'primary' }}"
    x-bind:disabled="disableButtons"
    x-on:click.prevent="submit('addToCart')"
  >
    {{ trans('shop::app.products.view.add-to-cart') }}
  </x-shop::ui.button>

  @if ($showBuyNowButton)
    <x-shop::ui.button
      wire:loading.attr="disabled"
      wire:target="buyNow"
      icon="lucide-shopping-cart"
      :disabled="!$product->isSaleable(1)"
      variant="primary"
      x-bind:disabled="disableButtons"
      x-on:click.prevent="submit('buyNow')"
    >
      {{ trans('shop::app.products.view.buy-now') }}
    </x-shop::ui.button>
  @endif
</div>
