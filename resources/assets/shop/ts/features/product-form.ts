import type { Alpine as AlpineType } from 'alpinejs';

type Validator = () => boolean;

export interface ProductForm {
  disabled: boolean;
  validators: Validator[];
  setDisabled(disabled: boolean): void;
  addValidator(fn: Validator): void;
  validate(): boolean;
}

export default function (Alpine: AlpineType) {
  Alpine.store('productForm', {
    validators: [],

    disabled: false,

    setDisabled(disabled) {
      this.disabled = disabled;
    },

    addValidator(fn) {
      this.validators.push(fn);
    },

    validate() {
      return this.validators.every((fn) => fn());
    },
  } as ProductForm);

  Alpine.data('VisualBuyButtons', () => ({
    get disableButtons(): boolean {
      return (this.$store.productForm as ProductForm).disabled;
    },

    submit(action: string) {
      if (this.disableButtons) return;

      if (!(this.$store.productForm as ProductForm).validate()) return;

      this.$wire.call(action);
    },
  }));

  Alpine.data('VisualDownloadableOptions', () => ({
    links: [] as number[],
    showErrors: false,

    init() {
      (this.$store.productForm as ProductForm).addValidator(() => this.validate());

      this.$watch('links', () => {
        this.validate();
        (this.$store.productForm as ProductForm).setDisabled(this.showErrors);
      });
    },

    validate() {
      this.showErrors = this.links.length === 0;
      return !this.showErrors;
    },
  }));

  Alpine.data('VisualProductPrices', () => ({
    labelEl: null as HTMLLIElement | null,
    regularPriceEl: null as HTMLLIElement | null,
    finalPriceEl: null as HTMLLIElement | null,

    defaultFinalPrice: '',
    defaultRegularPrice: '',

    init() {
      this.labelEl = this.$root.querySelector('.price-label');
      this.finalPriceEl = this.$root.querySelector('.final-price');
      this.regularPriceEl = this.$root.querySelector('.regular-price');

      if (this.finalPriceEl) {
        this.defaultFinalPrice = this.finalPriceEl.textContent as string;
      }

      if (this.regularPriceEl) {
        this.defaultRegularPrice = this.regularPriceEl.textContent as string;
      }
    },

    bindings: {
      ['x-on:product-variant-change.window'](event: CustomEvent) {
        if (event.detail.variant) {
          const prices = event.detail.prices;
          this.labelEl!.style.display = 'none';
          this.finalPriceEl!.textContent = prices.final.formatted_price;

          if (parseInt(prices.regular.price, 10) > parseInt(prices.final.price, 10)) {
            if (this.regularPriceEl) {
              this.regularPriceEl.style.display = 'inline-block';
              this.regularPriceEl.textContent = prices.regular.formatted_price;
            }
          } else {
            this.regularPriceEl && (this.regularPriceEl.style.display = 'none');
          }
        } else {
          this.labelEl!.style.display = 'inline-block';
          this.finalPriceEl!.textContent = this.defaultFinalPrice;
        }
      },
    },
  }));
}
