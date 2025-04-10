@props(['name', 'title', 'address' => [], 'savedAddresses' => [], 'showUseForShippingCheckbox' => false, 'heading', 'footer'])

@php
  $countries = core()->countries();
  $states = core()->groupedStatesByCountries();

  $addressName = $name . 'Address';

  $alpineProps = [
      'name' => $addressName,
      'selectedCountry' => '',
      'statesByCountry' => $states,
      'initialAddress' => $address,
      'showAddressFields' => count($savedAddresses) === 0,
  ];
@endphp

<div x-data x-address-form="@js($alpineProps)">
  <!-- Header section -->
  <div class="flex items-center justify-between">
    <h3 class="text-base font-medium text-neutral-600 md:text-xl">
      {{ $title }}
    </h3>
    @if (count($savedAddresses) > 0)
      <button
        x-address-form:cancel-trigger
        type="button"
        class="text-secondary hover:text-primary flex items-center gap-1 text-xs transition-colors"
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
          <div x-address-form:address="@js($address)"
            class="has-[:checked]:border-primary has-[:checked]:bg-primary-50 rounded-lg border-2 border-neutral-200 p-4 transition-colors hover:border-neutral-300"
          >
            <div class="mb-2 flex items-center justify-between">
              <div class="flex items-center">
                <input
                  id="{{ $name }}[id]"
                  type="radio"
                  name="{{ $name }}[id]"
                  wire:model.number="{{ $addressName }}.id"
                  value="{{ $address->id }}"
                  x-address-form:item-trigger
                >
                <label for="{{ $name }}[id]" class="text-secondary ml-2 font-medium">
                  {{ $address->first_name }} {{ $address->last_name }}
                </label>
              </div>

              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="text-secondary hover:text-primary transition-colors"
                  x-address-form:address-edit
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
          x-address-form:add-trigger
        >
          <x-lucide-plus class="text-secondary h-5 w-5" />
          @lang('shop::app.checkout.onepage.address.add-new-address')
        </button>
      </div>
    </template>

    <template x-if="$addressForm.showAddressFields">
      <div class="space-y-4">
        <x-shop::ui.form.input
          type="text"
          name="{{ $name }}[company_name]"
          wire:model="{{ $addressName }}.company_name"
          placeholder="{{ trans('shop::app.checkout.onepage.address.company-name') }}"
          :label="trans('shop::app.checkout.onepage.address.company-name')"
        />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <x-shop::ui.form.input
            type="text"
            required
            name="{{ $name }}.first_name"
            wire:model="{{ $addressName }}.first_name"
            :label="trans('shop::app.checkout.onepage.address.first-name')"
            :placeholder="trans('shop::app.checkout.onepage.address.first-name')"
          />

          <x-shop::ui.form.input
            type="text"
            required
            name="{{ $name }}.last_name"
            wire:model="{{ $addressName }}.last_name"
            :label="trans('shop::app.checkout.onepage.address.last-name')"
            :placeholder="trans('shop::app.checkout.onepage.address.last-name')"
          />
        </div>

        <x-shop::ui.form.input
          type="email"
          required
          name="{{ $name }}.email"
          wire:model="{{ $addressName }}.email"
          :label="trans('shop::app.checkout.onepage.address.email')"
          :placeholder="trans('shop::app.checkout.onepage.address.email')"
        />

        <x-shop::ui.form.input
          type="text"
          required
          name="{{ $name }}.address.0"
          wire:model="{{ $addressName }}.address.0"
          :label="trans('shop::app.checkout.onepage.address.street-address')"
          :placeholder="trans('shop::app.checkout.onepage.address.street-address')"
        />

        @if (core()->getConfigData('customer.address.information.street_lines') > 1)
          @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
            <x-shop::ui.form.input
              type="text"
              name="{{ $name }}.address.{{ $i }}"
              wire:model="{{ $addressName }}.address.{{ $i }}"
              :placeholder="trans('shop::app.checkout.onepage.address.street-address')"
            />
          @endfor
        @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <x-shop::ui.form.select
            autocomplete="off"
            name="{{ $name }}.country"
            wire:model="{{ $addressName }}.country"
            :required="core()->isCountryRequired()"
            :label="trans('shop::app.checkout.onepage.address.country')"
            :placeholder="trans('shop::app.checkout.onepage.address.country')"
            x-address-form:country=""
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

          <template x-if="$addressForm.haveStates">
            <x-shop::ui.form.select
              name="{{ $name }}.state"
              wire:model="{{ $addressName }}.state"
              :required="core()->isStateRequired()"
              :label="trans('shop::app.customers.account.addresses.create.state')"
            >
              <option value="" @disabled(core()->isStateRequired())>
                @lang('shop::app.checkout.onepage.address.select-state')
              </option>
              <template x-for="(state, index) in $addressForm.states">
                <option x-bind:value="state.code" x-text="state.default_name"></option>
              </template>
            </x-shop::ui.form.select>
          </template>
          <template x-if="!$addressForm.haveStates">
            <x-shop::ui.form.input
              type="text"
              name="{{ $name }}.state"
              wire:model="{{ $addressName }}.state"
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
            wire:model="{{ $addressName }}.city"
            :label="trans('shop::app.checkout.onepage.address.city')"
            :placeholder="trans('shop::app.checkout.onepage.address.city')"
          />

          <x-shop::ui.form.input
            type="text"
            required
            name="{{ $name }}.postcode"
            wire:model="{{ $addressName }}.postcode"
            :label="trans('shop::app.checkout.onepage.address.postcode')"
            :placeholder="trans('shop::app.checkout.onepage.address.postcode')"
          />
        </div>

        <x-shop::ui.form.input
          type="tel"
          required
          name="{{ $name }}.phone"
          wire:model="{{ $addressName }}.phone"
          :label="trans('shop::app.checkout.onepage.address.telephone')"
          :placeholder="trans('shop::app.checkout.onepage.address.telephone')"
        />

        @auth('customer')
          <label class="inline-flex items-center">
            <input
              type="checkbox"
              name="{{ $name }}[save_address]"
              wire:model="{{ $addressName }}.save_address"
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
          name="{{ $name }}[use_for_shipping]"
          wire:model="{{ $addressName }}.use_for_shipping"
          x-model="useBillingAddressForShipping"
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
