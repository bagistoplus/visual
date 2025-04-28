<x-shop::ui.accordion {{ $attributes }}>
  <x-shop::ui.accordion.item title="Estimate shipping" class="bg-surface border-surface-alt-600 rounded-lg !border !border-solid !py-1 px-3">
    <form class="pb-2">
      @csrf
      <p class="mb-4 text-sm">
        @lang('shop::app.checkout.cart.summary.estimate-shipping.info')
      </p>
      <div class="space-y-4">
        <div>
          <label
            for="country"
            class="mb-1 block text-sm font-medium"
            @if (core()->isCountryRequired()) required @endif
          >
            @lang('shop::app.checkout.cart.summary.estimate-shipping.country')
          </label>

          <select
            id="country"
            name="country"
            wire:model.live="country"
            placeholder="{{ trans('shop::app.checkout.cart.summary.estimate-shipping.country') }}"
          >
            <option value="">
              @lang('shop::app.checkout.cart.summary.estimate-shipping.select-country')
            </option>

            @foreach (core()->countries() as $countryItem)
              <option value="{{ $countryItem->code }}">
                {{ $countryItem->name }}
              </option>
            @endforeach
          </select>

          @error('country')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label
            for="state"
            class="mb-1 block text-sm font-medium"
            @if (core()->isStateRequired()) required @endif
          >
            @lang('shop::app.checkout.cart.summary.estimate-shipping.state')
          </label>

          @isset($states[$country])
            <select
              id="state"
              name="state"
              wire:model.live="state"
              placeholder="{{ trans('shop::app.checkout.cart.summary.estimate-shipping.state') }}"
            >
              <option value="">
                @lang('shop::app.checkout.cart.summary.estimate-shipping.select-state')
              </option>

              @foreach ($states[$country] as $stateItem)
                <option value="{{ $stateItem->code }}">
                  {{ $stateItem->default_name }}
                </option>
              @endforeach
            </select>
          @else
            <input
              id="state"
              type="text"
              name="state"
              wire:model.live="state"
              placeholder="{{ trans('shop::app.checkout.cart.summary.estimate-shipping.state') }}"
            >
          @endisset

          @error('state')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label
            for="postcode"
            class="mb-1 block text-sm font-medium"
            @if (core()->isPostCodeRequired()) required @endif
          >
            @lang('shop::app.checkout.cart.summary.estimate-shipping.postcode')
          </label>

          <input
            id="postcode"
            type="text"
            name="postcode"
            wire:model.live="postcode"
            placeholder="{{ trans('shop::app.checkout.cart.summary.estimate-shipping.postcode') }}"
          >

          @error('postcode')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>

        @if (count($shippingMethods) > 0)
          <div class="rounded-lg border">
            @foreach ($shippingMethods as $shippingMethod)
              @foreach ($shippingMethod['rates'] as $rate)
                <label class="flex cursor-pointer justify-start gap-4 border-b p-3 last:border-b-0">
                  <input
                    type="radio"
                    value={{ $rate['method'] }}
                    wire:model.live="shippingMethod"
                  >
                  <div class="-mt-1 flex flex-1 flex-col gap-2">
                    <span>{{ $rate['base_formatted_price'] }}</span>
                    <span>{{ $rate['method_title'] }}</span>
                  </div>
                </label>
              @endforeach
            @endforeach
          </div>
        @endif
      </div>
    </form>
  </x-shop::ui.accordion.item>
</x-shop::ui.accordion>
