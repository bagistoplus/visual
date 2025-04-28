import type { AlpineComponent } from 'alpinejs';
import { defineScope, defineComponent } from '../utils/define-component';

interface Product {
  id: number;
  name: string;
  qty: number;
  price: { final: { price: number } };
  is_default: boolean;
}

interface BundleOption {
  id: number;
  label: string;
  type: 'select' | 'radio' | 'checkbox' | 'multiselect';
  is_required: boolean;
  products: Product[];
}

interface ProductBundleState {
  options: BundleOption[];
  selectedProducts: Record<number, any>;
  quantities: Record<number, number>;

  totalPrice: number;
  formattedTotalPrice: string;

  init(): void;
  productIsSelected(optionId: number, productId: number): boolean;
  onQuantityChange(optionId: number, quantity: number): void;
  onSelectionChange(optionId: number, value?: any): void;
}

// ───────────────────────────────────────
// Scope: option
// ───────────────────────────────────────

interface ProductBundleOptionScope {
  option: BundleOption;

  isSelected(productId: number): boolean;
  updateSelection(value: any): void;
}

// ───────────────────────────────────────
// Scope: summaryItem
// ───────────────────────────────────────

interface ProductBundleSummaryItemScope {
  label: string;
  products: Product[];
  isSelected(productId: number): boolean;
}

type ProductBundleAPI = ProductBundleState & {
  $option: ProductBundleOptionScope;
  $summaryItem: ProductBundleSummaryItemScope;
};

// ───────────────────────────────────────
// Component Definition
// ───────────────────────────────────────

export default defineComponent<ProductBundleAPI>({
  name: 'product-bundle',

  setup(props) {
    return {
      options: props.options ?? [],
      selectedProducts: {},
      quantities: {},

      get totalPrice() {
        let total = 0;

        for (const option of this.options) {
          const selected = Array.isArray(this.selectedProducts[option.id])
            ? (this.selectedProducts[option.id] as number[])
            : [this.selectedProducts[option.id]];

          for (const product of option.products) {
            if (selected.includes(product.id)) {
              total += product.qty * product.price.final.price;
            }
          }
        }

        return total;
      },

      get formattedTotalPrice() {
        return (this as any).$formatPrice(this.totalPrice);
      },

      init() {
        this.options.forEach((option) => {
          const isMultiSelect = ['checkbox', 'multiselect'].includes(option.type);
          this.selectedProducts[option.id] = isMultiSelect ? [] : '';

          option.products.forEach((product) => {
            if (product.is_default) {
              if (isMultiSelect) {
                (this.selectedProducts[option.id] as number[]).push(product.id);
              } else {
                this.selectedProducts[option.id] = product.id;
              }
            }
          });

          if (['select', 'radio'].includes(option.type)) {
            const selected = option.products.find((p) => p.id === this.selectedProducts[option.id]);
            this.quantities[option.id] = selected ? selected.qty : 0;
          }
        });
      },

      productIsSelected(optionId, productId) {
        const selected = this.selectedProducts[optionId];
        return Array.isArray(selected) ? selected.includes(productId) : selected === productId;
      },

      onQuantityChange(optionId, quantity) {
        this.quantities[optionId] = quantity;

        const option = this.options.find((opt) => opt.id === optionId);
        const selected = option?.products.find((p) => p.id === this.selectedProducts[optionId]);

        if (selected) {
          selected.qty = quantity;
        }

        this.$wire.set('bundleProductQuantities', this.quantities, false);
      },

      onSelectionChange(optionId, _value) {
        const option = this.options.find((opt) => opt.id === optionId);

        if (option?.type === 'checkbox') {
          (this.$store.productForm as any).setDisabled((this.selectedProducts[optionId] as number[]).length === 0);
        }
      },
    };
  },

  parts: {
    option: defineScope<ProductBundleAPI, 'option', ProductBundleOptionScope>({
      name: 'option',
      setup(api, el, { value }) {
        const option = api.options.find((o) => o.id === Number(value))!;

        return {
          option,

          isSelected(productId) {
            return api.productIsSelected(option.id, productId);
          },

          updateSelection(value) {
            api.onSelectionChange(option.id, value);
          },
        };
      },

      bindings(api, scope) {
        return {
          'x-model.number': `selectedProducts[${scope.option.id}]`,
          'x-on:change': (event: Event) => scope.updateSelection((event.target as HTMLInputElement).value),
        };
      },
    }),

    summaryItem: defineScope<ProductBundleAPI, 'summaryItem', ProductBundleSummaryItemScope>({
      name: 'summaryItem',
      setup(api, _, ctx) {
        const option = api.options.find((o) => o.id === Number(ctx.value))!;
        return {
          label: option.label,
          products: option.products,
          isSelected(productId) {
            return api.productIsSelected(option.id, productId);
          },
        };
      },
      bindings(_, scope) {
        return {
          'x-show': () => scope.products.some((p) => scope.isSelected(p.id)),
        };
      },
    }),
  },
});
