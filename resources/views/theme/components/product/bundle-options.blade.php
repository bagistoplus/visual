@php
  $config = app('Webkul\Product\Helpers\BundleOption')->getBundleConfig($product);
@endphp

<div x-data x-product-bundle="@js(['options' => $config['options']])">
  <div class="grid gap-2">
    @foreach ($config['options'] as $option)
      <div class="grid gap-4 border-b pb-3 last:border-b-0">
        @if ($option['type'] === 'select')
          <label>
            <span class="text-sm font-semibold">{{ $option['label'] }}</span>
            <select
              class="mt-1 w-full"
              x-product-bundle:option="{{ $option['id'] }}"
              wire:model.number="bundleProductOptions.{{ $option['id'] }}"
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
        @elseif($option['type'] === 'multiselect')
          <label>
            <span class="text-sm font-semibold">{{ $option['label'] }}</span>
            <select
              multiple
              class="mt-1 w-full"
              x-product-bundle:option="{{ $option['id'] }}"
              wire:model.number="bundleProductOptions.{{ $option['id'] }}"
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
                  wire:model.number="bundleProductOptions.{{ $option['id'] }}"
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
                  x-product-bundle:option="{{ $option['id'] }}"
                  wire:model.number="bundleProductOptions.{{ $option['id'] }}"
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
                  x-product-bundle:option="{{ $option['id'] }}"
                  wire:model.number="bundleProductOptions.{{ $option['id'] }}"
                >
                <span>{{ $product['name'] }} + {{ $product['price']['final']['formatted_price'] }}</span>
              </label>
            @endforeach

            @if ($option['is_required'])
              <template x-if="selectedProducts[{{ $option['id'] }}].length <= 0">
                <span class="text-danger mt-1 text-xs italic">{{ $option['label'] }} is required</span>
              </template>
            @endif
          </div>
        @endif

        @php
          $defaultProduct = collect($option['products'])->firstWhere('is_default', true);
        @endphp
        @if (in_array($option['type'], ['select', 'radio']))
          <x-shop::quantity-selector
            :min="1"
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
      <li x-product-bundle:summary-item="option.id">
        <span x-text="option.label" class="font-semibold text-neutral-700"></span>
        <div>
          <template x-for="product in option.products" x-bind:key="product.id">
            <template x-if="$summaryItem.isSelected(product.id)">
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
