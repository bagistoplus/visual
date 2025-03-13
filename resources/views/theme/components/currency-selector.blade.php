@props(['mobile'])

@php
  $currencies = core()->getCurrentChannel()->currencies;
  $currentCurrency = core()->getCurrentCurrency();
@endphp

@if ($currencies->count() > 1)
  @isset($mobile)
    <label for="mobile-currency" class="text-secondary mb-2 block text-sm font-medium">Currency</label>
    <select
      id="mobile-currency"
      x-data
      @change="
        const url = new URL(window.location.href);
        url.searchParams.set('currency', $event.target.value);
        window.location.href = url.toString();
    "
    >
      @foreach ($currencies as $currency)
        <option value="{{ $currency['code'] }}" @if ($currency['code'] === $currentCurrency->code) selected @endif>
          {{ $currency['symbol'] }} {{ $currency['name'] }}
        </option>
      @endforeach
    </select>
  @else
    <div class="relative hidden sm:block" x-data="{ showCurrencyMenu: false }">
      <button class="hover:text-primary flex items-center p-2 transition-colors"
        @click="showCurrencyMenu = !showCurrencyMenu"
      >
        <span class="ml-1">
          {{ $currentCurrency->symbol }} {{ $currentCurrency->code }}
        </span>
      </button>
      <div
        x-cloak
        x-show="showCurrencyMenu"
        x-transition
        class="bg-surface absolute right-0 mt-2 w-48 rounded-lg py-2 shadow-lg"
        @click.outside="showCurrencyMenu = false"
      >
        @foreach ($currencies as $currency)
          <a href="{{ request()->fullUrlWithQuery(['currency' => $currency->code]) }}"
            class="hover:bg-surface-alt hover:text-primary block w-full px-4 py-2 text-left transition-colors"
          >
            {{ $currency['symbol'] }} {{ $currency['name'] }}
          </a>
        @endforeach
      </div>
    </div>
  @endisset
@endif
