@props([
    'name',
    'title',
    'countries' => [],
    'states' => [],
    'address' => [],
    'savedAddresses' => [],
    'showUseForShippingCheckbox' => false,
    'heading',
    'footer',
])

<div x-data="{
    showAddressFields: {{ count($savedAddresses) > 0 ? 'false' : 'true' }},
    fillAddressFields(name, address) {
        const fillable = ['id', 'company_name', 'email', 'first_name', 'last_name', 'address', 'country', 'state', 'city', 'postcode', 'phone'];

        fillable.forEach(key => {
            $wire.set(`${name}Address.${key}`, address[key], false);
        });
    },

    editAddress(name, address) {
        this.fillAddressFields(name, address);
        $wire.set(`${name}Address.save_address`, true, false);
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
        x-on:click="showAddressFields = false"
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
          <div
            class="has-[:checked]:border-primary has-[:checked]:bg-primary-50 rounded-lg border-2 border-neutral-200 p-4 transition-colors hover:border-neutral-300"
          >
            <div class="mb-2 flex items-center justify-between">
              <div class="flex items-center">
                <input
                  id="{{ $name }}Address[id]"
                  type="radio"
                  name="{{ $name }}Address[id]"
                  wire:model.number="{{ $name }}Address.id"
                  value="{{ $address['id'] }}"
                  x-on:click="fillAddressFields('{{ $name }}', @js($address))"
                >
                <label for="{{ $name }}Address[id]" class="text-secondary ml-2 font-medium">
                  {{ $address['first_name'] }} {{ $address['last_name'] }}
                </label>
              </div>

              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="text-secondary hover:text-primary transition-colors"
                  x-on:click="editAddress('{{ $name }}', @js($address))"
                >
                  Edit
                </button>
                @if ($address['default_address'])
                  <span class="bg-primary/10 text-primary rounded-full px-2 py-1 text-xs">Default</span>
                @endif
              </div>
            </div>

            <div class="text-secondary ml-6">
              @if ($address['company_name'])
                <p>{{ $address['company_name'] }}</p>
              @endif
              @if ($address['address'])
                <p>{{ implode(',', $address['address']) }}</p>
              @endif
              <p>{{ $address['city'] }}</p>
              <p>{{ $address['state'] }}, {{ $address['country'] }}</p>
              <p>{{ $address['postcode'] }}</p>
              <p class="mt-1 text-sm">{{ $address['phone'] }}</p>
            </div>
          </div>
        @endforeach

        <button
          type="button"
          class="text-secondary flex w-full items-center gap-2 rounded-lg border-2 border-dashed border-gray-200 p-4 transition-colors hover:border-gray-300 hover:bg-gray-50"
          x-on:click="showAddressFields = true"
        >
          <x-lucide-plus class="text-secondary h-5 w-5" />
          @lang('shop::app.checkout.onepage.address.add-new-address')
        </button>
      </div>
    </template>

    <template x-if="showAddressFields">
      <div class="space-y-4">
        <div>
          <label>
            <span class="text-sm font-semibold">
              @lang('shop::app.checkout.onepage.address.company-name')
            </span>
            <input
              type="text"
              class="mt-1"
              name="{{ $name }}Address[company_name]"
              wire:model="{{ $name }}Address.company_name"
              placeholder="{{ trans('shop::app.checkout.onepage.address.company-name') }}"
            >
          </label>
          @error($name . '.company_name')
            <span class="text-danger text-xs italic">{{ $message }}</span>
          @enderror
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label>
              <span class="text-sm font-semibold" required>
                @lang('shop::app.checkout.onepage.address.first-name')
              </span>
              <input
                type="text"
                class="mt-1"
                name="{{ $name }}Address[first_name]"
                wire:model="{{ $name }}Address.first_name"
                placeholder="{{ trans('shop::app.checkout.onepage.address.first-name') }}"
              >
            </label>
            @error($name . '.first_name')
              <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label>
              <span class="text-sm font-semibold" required>
                @lang('shop::app.checkout.onepage.address.last-name')
              </span>
              <input
                type="text"
                class="mt-1"
                name="{{ $name }}Address[last_name]"
                wire:model="{{ $name }}Address.last_name"
                placeholder="{{ trans('shop::app.checkout.onepage.address.last-name') }}"
              >
            </label>
            @error($name . '.last_name')
              <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div>
          <label>
            <span class="text-sm font-semibold" required>
              @lang('shop::app.checkout.onepage.address.email')
            </span>
            <input
              type="email"
              class="mt-1"
              name="{{ $name }}Address[email]"
              placeholder="{{ trans('shop::app.checkout.onepage.address.email') }}"
              wire:model="{{ $name }}Address.email"
            >
          </label>
          @error($name . '.email')
            <span class="text-danger text-xs italic">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label>
            <span class="text-sm font-semibold" required>
              @lang('shop::app.checkout.onepage.address.street-address')
            </span>
            <input
              type="text"
              class="mt-1"
              name="{{ $name }}Address[address][0]"
              wire:model="{{ $name }}Address.address.0"
              placeholder="{{ trans('shop::app.checkout.onepage.address.street-address') }}"
            >
          </label>
          @error($name . '.address.0')
            <span class="text-danger text-xs italic">{{ $message }}</span>
          @enderror
        </div>

        @if (core()->getConfigData('customer.address.information.street_lines') > 1)
          @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
            <div>
              <label>
                <input
                  type="text"
                  class="mt-1"
                  name="{{ $name }}Address[address][{{ $i }}]"
                  wire:model="{{ $name }}Address.address.{{ $i }}"
                  placeholder="{{ trans('shop::app.checkout.onepage.address.street-address') }} Line {{ $i + 1 }}"
                >
              </label>
            </div>
          @endfor
        @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label>
              <span class="text-sm font-semibold" required>
                @lang('shop::app.checkout.onepage.address.country')
              </span>
              <select
                class="mt-1"
                name="{{ $name }}Address[country]"
                wire:model.live="{{ $name }}Address.country"
              >
                <option disabled value="">
                  @lang('shop::app.checkout.onepage.address.select-country')
                </option>
                @foreach ($countries as $country)
                  <option value="{{ $country->code }}">
                    {{ $country->name }}
                  </option>
                @endforeach
              </select>
            </label>
            @error($name . '.country')
              <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label>
              <span class="text-sm font-semibold" @if (core()->isStateRequired()) required @endif>
                @lang('shop::app.checkout.onepage.address.state')
              </span>
              @if (isset($address['country']) && isset($states[$address['country']]))
                <select
                  class="mt-1"
                  name="{{ $name }}Address[state]"
                  wire:model.live="{{ $name }}Address.state"
                >
                  <option value="" @if (core()->isStateRequired()) disabled @endif>
                    @lang('shop::app.checkout.onepage.address.select-state')
                  </option>
                  @foreach ($states[$address['country']] as $state)
                    <option value="{{ $state->code }}">
                      {{ $state->default_name }}
                    </option>
                  @endforeach
                </select>
              @else
                <input
                  type="text"
                  class="mt-1"
                  name="{{ $name }}Address[state]"
                  placeholder="{{ trans('shop::app.checkout.onepage.address.state') }}"
                  wire:model="{{ $name }}Address.state"
                >
              @endif
            </label>
            @error($name . '.state')
              <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label>
              <span class="text-sm font-semibold" required>
                @lang('shop::app.checkout.onepage.address.city')
              </span>
              <input
                type="text"
                class="mt-1"
                name="{{ $name }}Address[city]"
                wire:model="{{ $name }}Address.city"
                placeholder="{{ trans('shop::app.checkout.onepage.address.city') }}"
              >
            </label>
            @error($name . '.city')
              <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label>
              <span class="text-sm font-semibold" @if (core()->isPostCodeRequired()) required @endif>
                @lang('shop::app.checkout.onepage.address.postcode')
              </span>
              <input
                type="text"
                class="mt-1"
                name="{{ $name }}[postcode]"
                wire:model="{{ $name }}Address.postcode"
                placeholder="{{ trans('shop::app.checkout.onepage.address.postcode') }}"
              >
            </label>
            @error($name . '.postcode')
              <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div>
          <label>
            <span class="text-sm font-semibold" required>
              @lang('shop::app.checkout.onepage.address.telephone')
            </span>
            <input
              type="text"
              class="mt-1"
              name="{{ $name }}Address[phone]"
              wire:model="{{ $name }}Address.phone"
              placeholder="{{ trans('shop::app.checkout.onepage.address.telephone') }}"
            >
          </label>
          @error($name . '.phone')
            <span class="text-danger text-xs italic">{{ $message }}</span>
          @enderror
        </div>

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
