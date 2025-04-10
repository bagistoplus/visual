import { defineScope, defineComponent } from '../utils/define-component';
import { ProductForm } from './product-form';

interface VariantAttribute {
  id: number;
  label: string;
  swatch_type?: string;
  options: VariantOption[];
}

interface VariantOption {
  id: number;
  label: string;
  swatch_value?: string;
  products: number[];
  isAvailable?: boolean;
}

interface VariantPickerProps {
  variantAttributes: VariantAttribute[];
  variantPrices: Record<number, any>;
  variantImages: Record<number, string[]>;
  variantVideos: Record<number, string[]>;
}

interface VariantPickerState extends VariantPickerProps {
  selections: Record<number, number>;
  matchingProducts: Set<number>;
  selectedVariant: number | null;

  isDropdownSwatch(swatchType?: string): boolean;
  findAttribute(id: number): VariantAttribute | undefined;
  findOption(attribute: VariantAttribute | undefined, optionId: number): VariantOption | undefined;
  findMatchingProducts(selections: Record<number, number>): Set<number>;
  updateMatchingProducts(): void;
  updateOptionAvailability(): void;
  onOptionSelected(attributeId: number, value: number | null): void;
  dispatchChange(): void;
}

interface AttributeScope extends VariantAttribute {
  selectedOptionId: number | undefined;
  isDropdown: boolean;
  select(value: number): void;
  getOptionState(optionId: number): {
    isSelected: boolean;
    isAvailable: boolean;
  };
}

type VariantPickerAPI = VariantPickerState & {
  $attribute: AttributeScope;
};

export default defineComponent<VariantPickerAPI>({
  name: 'variant-picker',

  setup(props) {
    const variantAttributes = props.variantAttributes ?? [];
    const variantPrices = props.variantPrices ?? {};
    const variantImages = props.variantImages ?? {};
    const variantVideos = props.variantVideos ?? {};

    const firstOption = variantAttributes[0]?.options[0];
    const firstVariantId = firstOption?.products[0];

    const defaultSelections: Record<number, number> = {};
    if (firstVariantId) {
      // Build default selections based on the first variant
      for (const variant of variantAttributes) {
        const option = variant.options.find((o) => o.products.includes(firstVariantId));
        if (option) {
          defaultSelections[variant.id] = option.id;
        }
      }
    }

    const selections = defaultSelections;
    const matchingProducts = new Set<number>();

    return {
      variantAttributes,
      variantPrices,
      variantImages,
      variantVideos,

      selections,
      matchingProducts,

      get selectedVariant() {
        // Only return a variant if all attributes have a selection
        if (Object.keys(this.selections).length !== this.variantAttributes.length) {
          return null;
        }

        const [variantId] = this.matchingProducts;

        return variantId ?? null;
      },

      // Default fallback is dropdown
      isDropdownSwatch(swatchType?: string) {
        return !swatchType || swatchType === 'dropdown';
      },

      findAttribute(id: number) {
        return this.variantAttributes.find((attr) => attr.id === id);
      },

      findOption(attribute, optionId) {
        return attribute?.options.find((o) => o.id === optionId);
      },

      findMatchingProducts(selections) {
        const products = new Set<number>();
        let isFirst = true;

        for (const [id, value] of Object.entries(selections)) {
          const attr = this.findAttribute(Number(id));
          const option = this.findOption(attr, value);

          if (!option) return new Set();

          if (isFirst) {
            option.products.forEach((p) => products.add(p));
            isFirst = false;
          } else {
            for (const p of Array.from(products)) {
              if (!option.products.includes(p)) products.delete(p);
            }
          }
        }

        return products;
      },

      updateMatchingProducts() {
        this.matchingProducts = this.findMatchingProducts(this.selections);
        this.updateOptionAvailability();
      },

      updateOptionAvailability() {
        for (const attr of this.variantAttributes) {
          for (const option of attr.options) {
            const otherSelections = { ...this.selections };
            delete otherSelections[attr.id];

            // If no other selections, all options are available
            if (Object.keys(otherSelections).length === 0) {
              option.isAvailable = true;
              continue;
            }

            // Check if this option is compatible with other selections
            const matching = this.findMatchingProducts(otherSelections);
            option.isAvailable = option.products.some((id) => matching.has(id));
          }
        }
      },

      onOptionSelected(attributeId, value) {
        value = Number.isNaN(Number(value)) ? null : Number(value);

        if (value === null || this.selections[attributeId] === value) {
          // Unselect
          delete this.selections[attributeId];
        } else {
          this.selections[attributeId] = value;
        }

        if (this.$wire) {
          this.$wire.set('variantAttributes', this.selections, false);
        }

        this.updateMatchingProducts();
        this.dispatchChange();
      },

      dispatchChange() {
        const [variantId] = this.matchingProducts;

        this.$dispatch('change', {
          selections: { ...this.selections },
          matchingProducts: Array.from(this.matchingProducts),
        });

        this.$dispatch('variant-medias-change', {
          images: variantId ? this.variantImages[variantId] : [],
          videos: variantId ? this.variantVideos[variantId] : [],
        });

        this.$dispatch('product-variant-change', {
          variant: this.selectedVariant,
          ...(this.selectedVariant && {
            prices: this.variantPrices[this.selectedVariant],
            images: this.variantImages[this.selectedVariant],
            videos: this.variantVideos[this.selectedVariant],
          }),
        });

        (this.$store.productForm as ProductForm).setDisabled(!this.selectedVariant);

        if (this.$wire) {
          this.$wire.set('selectedVariant', this.selectedVariant, false);
        }
      },

      init() {
        this.updateMatchingProducts();

        if (this.$wire) {
          this.$wire.set('variantAttributes', this.selections, false);
          this.$wire.set('selectedVariant', this.selectedVariant, false);
        }

        document.addEventListener('cart_updated', () => {
          this.$nextTick(() => {
            this.dispatchChange();
          });
        });
      },
    };
  },

  parts: {
    attribute: defineScope<VariantPickerAPI, 'attribute', AttributeScope>({
      name: 'attribute',
      setup(api, el, { value: attribute }: { value: VariantAttribute }) {
        return {
          ...attribute,

          get selectedOptionId() {
            return api.selections[attribute.id];
          },

          get isDropdown() {
            return api.isDropdownSwatch(attribute.swatch_type);
          },

          select(value) {
            api.onOptionSelected(attribute.id, value);
          },

          getOptionState(optionId) {
            const isSelected = api.selections[attribute.id] === optionId;
            const option = attribute.options.find((o) => o.id === optionId);
            return {
              isSelected,
              isAvailable: option?.isAvailable ?? true,
            };
          },
        };
      },
      bindings(api, scope) {
        return {
          'x-bind:data-attribute-id': () => scope.id,
          'x-bind:data-swatch-type': () => scope.swatch_type,
          'x-bind:data-selected': () => scope.selectedOptionId,
        };
      },
    }),
  },
});
