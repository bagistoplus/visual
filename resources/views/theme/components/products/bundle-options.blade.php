@php
  $config = app('Webkul\Product\Helpers\BundleOption')->getBundleConfig($product);
@endphp

@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', function() {
      Alpine.data('VisualProductBundle', () => ({
        config: @json($config),
        options: [],
        selectedProducts: {},
        quantities: {},

        get totalPrice() {
          let total = 0;

          for (const option of this.options) {
            const selectedProductIds = Array.isArray(this.selectedProducts[option.id]) ? this
              .selectedProducts[option.id] : [this.selectedProducts[option.id]];

            for (const product of option.products) {
              if (selectedProductIds.includes(product.id)) {
                total += product.qty * product.price.final.price;
              }
            }
          }

          return total;
        },

        get formattedTotalPrice() {
          return this.$formatPrice(this.totalPrice);
        },

        init() {
          this.options = this.config.options.slice(0);

          this.options.forEach(option => {
            const isMultiSelect = ['checkbox', 'multiselect'].includes(option.type);
            this.selectedProducts[option.id] = isMultiSelect ? [] : '';

            option.products.forEach(product => {
              if (product.is_default) {
                if (isMultiSelect) {
                  this.selectedProducts[option.id].push(product.id);
                } else {
                  this.selectedProducts[option.id] = product.id;
                }
              }
            });

            if (['select', 'radio'].includes(option.type)) {
              const selectedProduct = option.products.find(product => product.id === this.selectedProducts[
                option.id]);

              this.quantities[option.id] = selectedProduct ? selectedProduct.qty : 0;
            }
          });
        },

        onQuantityChange(optionId, quantity) {
          this.quantities[optionId] = quantity;
          const product = this.options.find(option => option.id === optionId).products
            .find(product => product.id === this.selectedProducts[optionId]);

          if (product) {
            product.qty = quantity;
          }
        }
      }));
    })
  </script>
@endpushOnce

<div x-data="VisualProductBundle">
  <div class="grid gap-2">
    @foreach ($config['options'] as $option)
      <div class="grid gap-4 border-b pb-3 last:border-b-0">
        @if ($option['type'] === 'select')
          <label>
            <span class="text-sm font-semibold">{{ $option['label'] }}</span>
            <select class="mt-1 w-full" x-model.number="selectedProducts[{{ $option['id'] }}]">
              @if (!$option['is_required'])
                <option value="">
                  @lang('shop::app.products.view.type.bundle.none')
                </option>
              @endif

              @foreach ($option['products'] as $product)
                <option value="{{ $product['id'] }}">
                  {{ $product['name'] }} + {{ $product['price']['final']['formatted_price'] }}
                </option>
              @endforeach
            </select>
          </label>
        @elseif($option['type'] === 'multiselect')
          <label>
            <span class="text-sm font-semibold">{{ $option['label'] }}</span>
            <select
              class="mt-1 w-full"
              x-model.number="selectedProducts[{{ $option['id'] }}]"
              multiple
            >
              @if (!$option['is_required'])
                <option value="">
                  @lang('shop::app.products.view.type.bundle.none')
                </option>
              @endif

              @foreach ($option['products'] as $product)
                <option value="{{ $product['id'] }}">
                  {{ $product['name'] }} + {{ $product['price']['final']['formatted_price'] }}
                </option>
              @endforeach
            </select>
          </label>
        @elseif ($option['type'] === 'radio')
          <div class="space-y-2">
            <span class="text-sm font-semibold">{{ $option['label'] }}</span>

            @if (!$option['is_required'])
              <label class="flex items-center gap-2">
                <input
                  type="radio"
                  value=""
                  name="bundle_options[{{ $option['id'] }}][]"
                >
                <span>@lang('shop::app.products.view.type.bundle.none')</span>
              </label>
            @endif

            @foreach ($option['products'] as $product)
              <label class="flex items-center gap-2">
                <input
                  type="radio"
                  name="bundle_options[{{ $option['id'] }}][]"
                  value="{{ $product['id'] }}"
                  x-model.number="selectedProducts[{{ $option['id'] }}]"
                >
                <span>{{ $product['name'] }} + {{ $product['price']['final']['formatted_price'] }}</span>
              </label>
            @endforeach
          </div>
        @elseif($option['type'] === 'checkbox')
          <div class="space-y-2">
            <span class="text-sm font-semibold">{{ $option['label'] }}</span>

            @foreach ($option['products'] as $product)
              <label class="flex items-center gap-2">
                <input
                  type="checkbox"
                  name="bundle_options[{{ $option['id'] }}][]"
                  value="{{ $product['id'] }}"
                  x-model.number="selectedProducts[{{ $option['id'] }}]"
                >
                <span>{{ $product['name'] }} + {{ $product['price']['final']['formatted_price'] }}</span>
              </label>
            @endforeach
          </div>
        @endif

        @php
          $defaultProduct = collect($option['products'])->firstWhere('is_default', true);
        @endphp
        @if (in_array($option['type'], ['select', 'radio']))
          <x-shop::quantity-selector
            not
            :value="$defaultProduct ? $defaultProduct['qty'] : 0"
            x-on:change="onQuantityChange({{ $option['id'] }}, $event.detail)"
          />
        @endif
      </div>
    @endforeach
  </div>

  <hr class="my-4">

  <div class="flex items-center justify-between">
    <label>@lang('shop::app.products.view.type.bundle.total-amount')</label>
    <div class="text-primary text-xl font-medium" x-text="formattedTotalPrice"></div>
  </div>

  <ul class="mt-2 space-y-3">
    <template x-for="(option) in options" x-bind:key="option.id">
      <li>
        <span x-text="option.label" class="font-semibold text-neutral-700"></span>
        <div>
          <template x-for="product in option.products" x-bind:key="product.id">
            <template
              x-if="selectedProducts[option.id] === product.id || selectedProducts[option.id].includes(product.id)"
            >
              <div>
                <span x-text="product.qty"></span>
                x
                <span x-text="product.name"></span>
              </div>
            </template>
          </template>
        </div>
      </li>
    </template>
  </ul>
</div>
