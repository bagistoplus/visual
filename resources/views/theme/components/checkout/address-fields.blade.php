@props(['name', 'title', 'address' => [], 'savedAddresses' => [], 'showUseForShippingCheckbox' => false, 'heading', 'footer'])

@php
  $countries = core()->countries();
  $states = core()->groupedStatesByCountries();
@endphp

<div x-data="{
    initialAddress: @js($address),
    addressType: '{{ $name }}',
    showAddressFields: {{ count($savedAddresses) > 0 ? 'false' : 'true' }},

    selectedCountry: '',
    countryStates: @js($states),

    get selectedCountryHaveStates() {
        return this.countryStates[this.selectedCountry] &&
            this.countryStates[this.selectedCountry].length > 0
    },

    fillAddressFields(address) {
        newAddress = {};

        Object.keys(this.initialAddress).forEach(key => {
            newAddress[key] = address[key] ?? '';
        });

        this.selectedCountry = newAddress.country;
        $wire.set(`${this.addressType}Address`, newAddress, false);
    },

    resetInitialAddress() {
        this.fillAddressFields({ ...this.initialAddress });
    },

    clearAddressFields() {
        this.initialAddress = { ...this.$wire[`${ this.addressType }Address`] };

        this.fillAddressFields({
            address: [],
            use_for_shipping: this.initialAddress.use_for_shipping,
            save_address: this.initialAddress.save_address,
        });
    },

    editAddress(address) {
        this.fillAddressFields(address);
        this.showAddressFields = true;
    }
}">
  <!-- Header section -->
  <div class="flex items-center justify-between">
    <h3 class="text-base font-medium text-neutral-600 md:text-xl">
      {{ $title }}
    </h3>
    @if (count($savedAddresses) > 0)
      <button
        type="button"
        class="text-secondary hover:text-primary flex items-center gap-1 text-xs transition-colors"
        x-on:click="showAddressFields = false; resetInitialAddress();"
        x-show="showAddressFields"
      >
        <x-lucide-chevron-left class="h-4 w-4" />
        <span>Back</span>
      </button>
    @endif
  </div>

  <div class="mt-2 grid gap-4">
    @if (isset($heading) && $heading->hasActualContent())
      {{ $heading }}
    @endif

    <template x-if="!showAddressFields">
      <div class="space-y-4">
        @foreach ($savedAddresses as $address)
          <div class="has-[:checked]:border-primary has-[:checked]:bg-primary-50 rounded-lg border-2 border-neutral-200 p-4 transition-colors hover:border-neutral-300">
            <div class="mb-2 flex items-center justify-between">
              <div class="flex items-center">
                <input
                  id="{{ $name }}Address[id]"
                  type="radio"
                  name="{{ $name }}Address[id]"
                  wire:model.number="{{ $name }}Address.id"
                  value="{{ $address->id }}"
                  x-on:click="fillAddressFields(@js($address))"
                >
                <label for="{{ $name }}Address[id]" class="text-secondary ml-2 font-medium">
                  {{ $address->first_name }} {{ $address->last_name }}
                </label>
              </div>

              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="text-secondary hover:text-primary transition-colors"
                  x-on:click="editAddress(@js($address))"
                >
                  Edit
                </button>
                @if ($address->default_address)
                  <span class="bg-primary/10 text-primary rounded-full px-2 py-1 text-xs">Default</span>
                @endif
              </div>
            </div>

            <div class="text-secondary ml-6">
              @if ($address->company_name)
                <p>{{ $address->company_name }}</p>
              @endif
              @if ($address->address)
                <p>{{ implode(',', $address->address) }}</p>
              @endif
              <p>{{ $address->city }}</p>
              <p>{{ $address->state }}, {{ $address->country }}</p>
              <p>{{ $address->postcode }}</p>
              <p class="mt-1 text-sm">{{ $address->phone }}</p>
            </div>
          </div>
        @endforeach

        <button
          type="button"
          class="text-secondary flex w-full items-center gap-2 rounded-lg border-2 border-dashed border-gray-200 p-4 transition-colors hover:border-gray-300 hover:bg-gray-50"
          x-on:click="clearAddressFields(); showAddressFields = true"
        >
          <x-lucide-plus class="text-secondary h-5 w-5" />
          @lang('shop::app.checkout.onepage.address.add-new-address')
        </button>
      </div>
    </template>

    <template x-if="showAddressFields">
      <div class="space-y-4">
        <x-shop::ui.form.input
          type="text"
          name="{{ $name }}[company_name]"
          wire:model="{{ $name }}Address.company_name"
          placeholder="{{ trans('shop::app.checkout.onepage.address.company-name') }}"
          :label="trans('shop::app.checkout.onepage.address.company-name')"
        />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <x-shop::ui.form.input
            type="text"
            required
            name="{{ $name }}.first_name"
            wire:model="{{ $name }}Address.first_name"
            :label="trans('shop::app.checkout.onepage.address.first-name')"
            :placeholder="trans('shop::app.checkout.onepage.address.first-name')"
          />

          <x-shop::ui.form.input
            type="text"
            required
            name="{{ $name }}.last_name"
            wire:model="{{ $name }}Address.last_name"
            :label="trans('shop::app.checkout.onepage.address.last-name')"
            :placeholder="trans('shop::app.checkout.onepage.address.last-name')"
          />
        </div>

        <x-shop::ui.form.input
          type="email"
          required
          name="{{ $name }}.email"
          wire:model="{{ $name }}Address.email"
          :label="trans('shop::app.checkout.onepage.address.email')"
          :placeholder="trans('shop::app.checkout.onepage.address.email')"
        />

        <x-shop::ui.form.input
          type="text"
          required
          name="{{ $name }}.address.0"
          wire:model="{{ $name }}Address.address.0"
          :label="trans('shop::app.checkout.onepage.address.street-address')"
          :placeholder="trans('shop::app.checkout.onepage.address.street-address')"
        />

        @if (core()->getConfigData('customer.address.information.street_lines') > 1)
          @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
            <x-shop::ui.form.input
              type="text"
              name="{{ $name }}.address.{{ $i }}"
              wire:model="{{ $name }}Address.address.{{ $i }}"
              :placeholder="trans('shop::app.checkout.onepage.address.street-address')"
            />
          @endfor
        @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <x-shop::ui.form.select
            autocomplete="off"
            name="{{ $name }}.country"
            x-model="selectedCountry"
            wire:model="{{ $name }}Address.country"
            :required="core()->isCountryRequired()"
            :label="trans('shop::app.checkout.onepage.address.country')"
            :placeholder="trans('shop::app.checkout.onepage.address.country')"
          >
            <option value="" @disabled(core()->isCountryRequired())>
              @lang('shop::app.checkout.onepage.address.select-country')
            </option>
            @foreach ($countries as $country)
              <option value="{{ $country->code }}">
                {{ $country->name }}
              </option>
            @endforeach
          </x-shop::ui.form.select>

          <template x-if="selectedCountryHaveStates">
            <x-shop::ui.form.select
              name="{{ $name }}.state"
              wire:model="{{ $name }}Address.state"
              :required="core()->isStateRequired()"
              :label="trans('shop::app.customers.account.addresses.create.state')"
            >
              <option value="" @disabled(core()->isStateRequired())>
                @lang('shop::app.checkout.onepage.address.select-state')
              </option>
              <template x-for="(state, index) in countryStates[selectedCountry]">
                <option x-bind:value="state.code" x-text="state.default_name"></option>
              </template>
            </x-shop::ui.form.select>
          </template>
          <template x-if="!selectedCountryHaveStates">
            <x-shop::ui.form.input
              type="text"
              name="{{ $name }}.state"
              wire:model="{{ $name }}Address.state"
              :required="core()->isStateRequired()"
              :label="trans('shop::app.checkout.onepage.address.state')"
              :placeholder="trans('shop::app.checkout.onepage.address.state')"
            />
          </template>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <x-shop::ui.form.input
            type="text"
            required
            name="{{ $name }}.city"
            wire:model="{{ $name }}Address.city"
            :label="trans('shop::app.checkout.onepage.address.city')"
            :placeholder="trans('shop::app.checkout.onepage.address.city')"
          />

          <x-shop::ui.form.input
            type="text"
            required
            name="{{ $name }}.postcode"
            wire:model="{{ $name }}Address.postcode"
            :label="trans('shop::app.checkout.onepage.address.postcode')"
            :placeholder="trans('shop::app.checkout.onepage.address.postcode')"
          />
        </div>

        <x-shop::ui.form.input
          type="tel"
          required
          name="{{ $name }}.phone"
          wire:model="{{ $name }}Address.phone"
          :label="trans('shop::app.checkout.onepage.address.telephone')"
          :placeholder="trans('shop::app.checkout.onepage.address.telephone')"
        />

        @auth('customer')
          <label class="inline-flex items-center">
            <input
              type="checkbox"
              name="{{ $name }}Address[save_address]"
              wire:model="{{ $name }}Address.save_address"
            >
            <span class="ml-2">
              {{ trans('shop::app.checkout.onepage.address.save-address') }}
            </span>
          </label>
        @endauth
      </div>
    </template>

    @if ($showUseForShippingCheckbox)
      <label class="inline-flex items-center">
        <input
          type="checkbox"
          name="{{ $name }}Address[use_for_shipping]"
          wire:model="{{ $name }}Address.use_for_shipping"
          x-model="useBillingAddressForShipping"
          x-on:change="$nextTick(() => console.log(useBillingAddressForShipping))"
        >
        <span class="ml-2">
          {{ trans('shop::app.checkout.onepage.address.same-as-billing') }}
        </span>
      </label>
    @endif

    @if (isset($footer) && $footer->hasActualContent())
      {{ $footer }}
    @endif
  </div>
</div>
