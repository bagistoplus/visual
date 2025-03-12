@props(['product'])

@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', function() {
      Alpine.data('VisualProductPrices', () => ({
        labelEl: null,
        regularPriceEl: null,
        finalPriceEl: null,

        defaultFinalPrice: '',
        defaultRegularPrice: '',

        init() {
          this.labelEl = this.$root.querySelector('.price-label');
          this.finalPriceEl = this.$root.querySelector('.final-price');
          this.regularPriceEl = this.$root.querySelector('.regular-price');

          if (this.finalPriceEl) {
            this.defaultFinalPrice = this.finalPriceEl.textContent;
          }

          if (this.regularPriceEl) {
            this.defaultRegularPrice = this.regularPriceEl.textContent;
          }
        },

        root: {
          ['@product-variant-change.window'](event) {
            if (event.detail.variant) {
              const prices = event.detail.prices;
              this.labelEl.style.display = 'none';
              this.finalPriceEl.textContent = prices.final.formatted_price;

              if (parseInt(prices.regular.price, 10) > parseInt(prices.final.price, 10)) {
                if (this.regularPriceEl) {
                  this.regularPriceEl.style.display = 'inline-block';
                  this.regularPriceEl.textContent = prices.regular.formatted_price;
                }
              } else {
                this.regularPriceEl && (this.regularPriceEl.style.display = 'none');
              }
            } else {
              this.labelEl.style.display = 'inline-block';
              this.finalPriceEl.textContent = this.defaultFinalPrice;
            }
          }
        }
      }));
    });
  </script>
@endpushOnce

<div
  x-data="VisualProductPrices"
  x-bind="root"
  class="text-primary [&>div>p:nth-of-type(2)]:text-neutral flex items-center gap-2 text-lg font-medium [&>div>p:nth-of-type(2)]:text-xs [&>div]:flex [&>div]:items-center [&_.line-through]:text-neutral-400"
>
  {!! $product->getTypeInstance()->getPriceHtml() !!}
</div>
