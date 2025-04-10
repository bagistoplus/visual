@props(['address' => null])

@php
  $props = [
      'selectedCountry' => old('country', optional($address)->country),
      'statesByCountry' => core()->groupedStatesByCountries(),
  ];
@endphp
<form
  {{ $attributes }}
  method="POST"
  x-data
  x-address-form="@js($props)"
>
  @csrf
  @isset($address)
    @method('PUT')
  @endisset

  <div class="space-y-4">
    <x-shop::ui.form.input
      type="text"
      name="company_name"
      :value="old('company_name', optional($address)->company_name)"
      :label="trans('shop::app.customers.account.addresses.create.company-name')"
    />

    <x-shop::ui.form.input
      type="text"
      name="vat_id"
      :value="old('vat_id', optional($address)->vat_id)"
      :label="trans('shop::app.customers.account.addresses.create.vat-id')"
    />
  </div>

  <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
    <x-shop::ui.form.input
      required
      type="text"
      name="first_name"
      :value="old('first_name', optional($address)->first_name)"
      :label="trans('shop::app.customers.account.addresses.create.first-name')"
    />

    <x-shop::ui.form.input
      required
      type="text"
      name="last_name"
      :value="old('last_name', optional($address)->last_name)"
      :label="trans('shop::app.customers.account.addresses.create.last-name')"
    />
  </div>

  <div class="mt-4 space-y-4">
    <x-shop::ui.form.input
      required
      type="text"
      name="email"
      :value="old('email', optional($address)->email)"
      :label="trans('shop::app.customers.account.addresses.create.email')"
      :placeholder="trans('shop::app.customers.account.addresses.create.email')"
    />

    @php
      $addresses = $address ? explode(PHP_EOL, $address->address) : [];
    @endphp

    <x-shop::ui.form.input
      required
      type="text"
      name="address[]"
      :value="collect(old('vat_id', $addresses))->first()"
      :label="trans('shop::app.customers.account.addresses.create.street-address')"
      :placeholder="trans('shop::app.customers.account.addresses.create.street-address')"
    />

    @if (core()->getConfigData('customer.address.information.street_lines') && core()->getConfigData('customer.address.information.street_lines') > 1)

      @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
        <x-shop::ui.form.input
          type="text"
          name="address[{{ $i }}]"
          :value="old('address[{{ $i }}]', $addresses[$i] ?? '')"
          :placeholder="trans('shop::app.customers.account.addresses.create.street-address') . ' ' . $i + 1"
        />
      @endfor
    @endif
  </div>

  <div class="mt-4 grid grid-cols-2 gap-4">
    @php
      $country = old('country', optional($address)->country);
      $state = old('state', optional($address)->state);
    @endphp

    <x-shop::ui.form.select
      name="country"
      :required="core()->isCountryRequired()"
      :label="trans('shop::app.customers.account.addresses.create.country')"
      x-address-form:country=""
    >
      <option
        value=""
        disabled
        @selected(!$country)
      >
        @lang('shop::app.customers.account.addresses.create.select-country')
      </option>

      @foreach (core()->countries() as $country)
        <option value="{{ $country->code }}" @selected($country === $country->code)>
          {{ $country->name }}
        </option>
      @endforeach
    </x-shop::ui.form.select>

    <template x-if="$addressForm.haveStates">
      <x-shop::ui.form.select
        name="state"
        :required="core()->isCountryRequired()"
        :value="$state"
        :label="trans('shop::app.customers.account.addresses.create.state')"
      >
        <template x-for="(state, index) in $addressForm.states">
          <option x-bind:value="state.code" x-text="state.default_name"></option>
        </template>
      </x-shop::ui.form.select>
    </template>

    <template x-if="!$addressForm.haveStates">
      <x-shop::ui.form.input
        required
        type="text"
        name="state"
        :value="$state"
        :label="trans('shop::app.customers.account.addresses.create.state')"
      />
    </template>

    <x-shop::ui.form.input
      required
      type="text"
      name="city"
      :value="old('city', optional($address)->city)"
      :label="trans('shop::app.customers.account.addresses.create.city')"
    />

    <x-shop::ui.form.input
      type="text"
      name="postcode"
      :required="core()->isPostCodeRequired()"
      :value="old('postcode', optional($address)->postcode)"
      :label="trans('shop::app.customers.account.addresses.create.post-code')"
    />
  </div>

  <div class="mt-4 space-y-4">
    <x-shop::ui.form.input
      required
      type="text"
      name="phone"
      :value="old('phone', optional($address)->phone)"
      :label="trans('shop::app.customers.account.addresses.create.phone')"
      :placeholder="trans('shop::app.customers.account.addresses.create.phone')"
    />

    <div>
      <label class="inline-flex items-center gap-2">
        <input
          type="checkbox"
          name="default_address"
          value="1"
        >
        <span>
          @lang('shop::app.customers.account.addresses.create.set-as-default')
        </span>
      </label>
    </div>

    <x-shop::ui.button type="submit" class="px-16">
      @isset($address)
        @lang('shop::app.customers.account.addresses.edit.update-btn')
      @else
        @lang('shop::app.customers.account.addresses.create.save')
      @endisset
    </x-shop::ui.button>
  </div>
</form>
