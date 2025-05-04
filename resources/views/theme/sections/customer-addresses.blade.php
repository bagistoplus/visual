<div class="bg-background rounded-lg shadow-sm">
  <div class="border-b border-neutral-100 p-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl text-neutral-700">
        @lang('shop::app.customers.account.addresses.index.title')
      </h1>
      <x-shop::ui.button
        color="primary"
        variant="soft"
        icon="lucide-plus"
        size="sm"
        href="{{ route('shop.customers.account.addresses.create') }}"
      >
        @lang('shop::app.customers.account.addresses.index.add-address')
      </x-shop::ui.button>
    </div>
  </div>
  <div class="p-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      @foreach ($addresses as $address)
        <div class="@if ($address->default_address) border-primary @else border-neutral-200 @endif rounded-lg border-2 p-6">
          <div class="mb-4 flex items-start justify-between">
            <div class="flex items-center gap-2">
              <span class="text-secondary font-medium capitalize">
                {{ $address->first_name }} {{ $address->last_name }}
              </span>

              @if ($address->default_address)
                <span class="bg-primary/10 text-primary rounded-full px-2 py-1 text-xs">Default</span>
              @endif
            </div>

            <div class="-mt-2 flex items-center gap-2">
              <x-shop::ui.button
                href="{{ route('shop.customers.account.addresses.edit', $address->id) }}"
                variant="ghost"
                icon="lucide-edit"
                size="sm"
                icon-only
                rounded
              />
              <form method="POST" action="{{ route('shop.customers.account.addresses.delete', $address->id) }}">
                @csrf
                @method('DELETE')

                <x-shop::ui.button
                  type="submit"
                  variant="ghost"
                  color="secondary"
                  icon="lucide-trash-2"
                  size="sm"
                  icon-only
                  rounded
                />
              </form>
            </div>
          </div>

          <div class="space-y-2">
            @if ($address->company_name)
              <p class="font-medium">{{ $address->company_name }}</p>
            @endif
            <p>
              {{ $address->address }},

              {{ $address->city }},
              {{ $address->state }}, {{ $address->country }},
              {{ $address->postcode }}
            </p>
          </div>

          @unless ($address->default_address)
            <form
              class="mt-4"
              method="POST"
              action="{{ route('shop.customers.account.addresses.update.default', $address->id) }}"
            >
              @csrf
              @method('PATCH')

              <x-shop::ui.button
                type="submit"
                variant="ghost"
                icon="lucide-check"
                size="sm"
              >
                @lang('shop::app.customers.account.addresses.index.set-as-default')
              </x-shop::ui.button>
            </form>
          @endunless
        </div>
      @endforeach
    </div>
  </div>
</div>
