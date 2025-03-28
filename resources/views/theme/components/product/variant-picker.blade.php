@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', function() {
      Alpine.data('VisualProductVariantPicker', () => ({
        variantAttributes: @json($variantAttributes),
        variantPrices: @json($variantPrices),
        variantImages: @json($variantImages),
        variantVideos: @json($variantVideos),

        selections: {},
        matchingProducts: new Set(),

        get selectedVariant() {
          if (Object.keys(this.selections).length !== this.variantAttributes.length) {
            return null;
          }

          const [variantId] = this.matchingProducts;
          return variantId;
        },

        init() {
          const firstOption = this.variantAttributes[0].options[0];
          if (firstOption) {
            const firstVariantId = firstOption.products[0];
            const defaultSelections = {};

            this.variantAttributes.forEach(variant => {
              const option = variant.options.find(o => o.products.includes(firstVariantId));
              if (option) {
                defaultSelections[variant.id] = option.id;
              }
            });

            this.selections = defaultSelections;

          }

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

        isDropdownSwatch(swatchType) {
          return !swatchType || swatchType === 'dropdown';
        },

        findAttribute(id) {
          return this.variantAttributes.find(attr => attr.id === id);
        },

        findOption(attribute, optionId) {
          return attribute?.options.find(o => o.id === optionId);
        },

        findMatchingProducts(selections) {
          const products = new Set();
          let isFirst = true;

          for (const [id, value] of Object.entries(selections)) {
            const attribute = this.findAttribute(Number(id));
            const option = this.findOption(attribute, value);

            if (!option) {
              return new Set();
            }

            if (isFirst) {
              option.products.forEach(p => products.add(p));
              isFirst = false;
            } else {
              Array.from(products).forEach(p => {
                if (!option.products.includes(p)) {
                  products.delete(p);
                }
              });
            }
          }

          return products;
        },

        updateMatchingProducts() {
          this.matchingProducts = this.findMatchingProducts(this.selections);
          this.updateOptionAvailability();
        },

        updateOptionAvailability() {
          this.variantAttributes.forEach(attribute => {
            attribute.options.forEach(option => {
              const otherSelections = {
                ...this.selections
              };
              delete otherSelections[attribute.id];

              // If no other selections, all options are available
              if (Object.keys(otherSelections).length === 0) {
                option.isAvailable = true;
                return;
              }

              // Check if this option is compatible with other selections
              const matchingProducts = this.findMatchingProducts(otherSelections);
              option.isAvailable = option.products.some(id => matchingProducts.has(id));
            });
          });
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
            })
          });

          Alpine.store('ProductForm').disableButtons = !this.selectedVariant;

          if (this.$wire) {
            this.$wire.set('selectedVariant', this.selectedVariant, false);
          }
        }
      }));
    });
  </script>
@endpushOnce

<div x-data="VisualProductVariantPicker">
  <template x-for="attribute in variantAttributes">
    <div class="mb-4 max-w-72">
      <label class="mb-1 block font-medium" x-text="attribute.label">
      </label>

      <template x-if="isDropdownSwatch(attribute.swatch_type)">
        <select
          x-bind:id="attribute.id"
          class="py-1"
          x-on:change="onOptionSelected(attribute.id, event.target.value)"
        >
          <option x-text="'Select ' + attribute.label"></option>
          <template x-for="option in attribute.options" x-bind:key="option.id">
            <option
              x-bind:selected="selections[attribute.id] == option.id"
              x-bind:value="option.id"
              x-bind:disabled="!option.isAvailable"
              x-text="option.label + (!option.isAvailable ? ' (Unavailable)' : '')"
            ></option>
          </template>
        </select>
      </template>

      <template x-if="attribute.swatch_type === 'color'">
        <div class="flex gap-4">
          <template x-for="option in attribute.options">
            <button
              x-bind:class="[
                  'w-8 h-8 border rounded-full flex items-center justify-center relative',
                  selections[attribute.id] === option.id ? 'ring-2 ring-offset-2 ring-primary' :
                  'hover:ring-2 hover:ring-offset-2 hover:ring-neutral-200',
                  !option.isAvailable ? 'cursor-not-allowed !ring-primary-300' : ''
              ]"
              x-bind:style="{ backgroundColor: option.swatch_value }"
              x-bind:title="option.label + (!option.isAvailable ? ' (Unavailable)' : '')"
              @click="onOptionSelected(attribute.id, option.id)"
            >
              <template x-if="!option.isAvailable">
                <div class="absolute inset-0 rounded-full bg-black/20"></div>
              </template>
            </button>
          </template>
        </div>
      </template>

      <template x-if="attribute.swatch_type === 'image'">
        <div class="flex gap-4">
          <template x-for="option in attribute.options">
            <button
              x-bind:class="[
                  'w-10 h-10 rounded-lg relative overflow-hidden',
                  selections[attribute.id] === option.id ? 'ring-2 ring-offset-2 ring-primary' :
                  'hover:ring-2 hover:ring-offset-2 hover:ring-neutral-200',
                  !option.isAvailable ? 'cursor-not-allowed' : ''
              ]"
              x-bind:title="option.label + (!option.isAvailable ? ' (Unavailable)' : '')"
              @click="onOptionSelected(attribute.id, option.id)"
            >
              <img x-bind:src="option.swatch_value" class="h-full w-full rounded-lg">
              <template x-if="!option.isAvailable">
                <div class="absolute inset-0 rounded-lg bg-black/30"></div>
              </template>
            </button>
          </template>
        </div>
      </template>

      <template x-if="attribute.swatch_type === 'text'">
        <div class="grid grid-cols-5 gap-2">
          <template x-for="option in attribute.options" :key="option.id">
            <button
              :class="[
                  'py-2 px-3 text-sm font-medium rounded-md relative',
                  selections[attribute.id] === option.id ? 'bg-primary text-primary-50' :
                  'bg-white text-gray-900 border border-neutral-200 hover:bg-neutral-50',
                  !option.isAvailable ? 'cursor-not-allowed text-neutral-300' : ''
              ]"
              :title="option.label + (!option.isAvailable ? ' (Unavailable)' : '')"
              @click="onOptionSelected(attribute.id, option.id)"
            >
              <span x-text="option.label"></span>
              <template x-if="!option.isAvailable">
                <div class="absolute inset-0 rounded-md bg-black/30"></div>
              </template>
            </button>
          </template>
        </div>
      </template>
    </div>
  </template>
</div>
