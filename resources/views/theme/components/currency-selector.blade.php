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
    <x-shop::ui.menu class="hidden sm:block">
      <x-shop::ui.menu.trigger>
        <button class="hover:text-primary flex items-center p-2 transition-colors">
          <span class="ml-1">
            {{ $currentCurrency->symbol }} {{ $currentCurrency->code }}
          </span>
        </button>
      </x-shop::ui.menu.trigger>
      <x-shop::ui.menu.items>
        @foreach ($currencies as $currency)
          <x-shop::ui.menu.item href="{{ request()->fullUrlWithQuery(['currency' => $currency->code]) }}">
            {{ $currency['symbol'] }} {{ $currency['name'] }}
          </x-shop::ui.menu.item>
        @endforeach
      </x-shop::ui.menu.items>
    </x-shop::ui.menu>
  @endisset
@endif
