<div class="rounded-lg bg-white shadow-sm">
  <div class="border-b border-neutral-100 p-4">
    <div class="flex items-center justify-between">
      <h1 class="font-serif text-2xl text-neutral-700">
        @lang('shop::app.customers.account.addresses.edit.edit')
        @lang('shop::app.customers.account.addresses.edit.title')
      </h1>
    </div>
  </div>

  <div class="p-6">
    <x-shop::customer-address-form :address="$address" :action="route('shop.customers.account.addresses.update', $address->id)" />
  </div>
</div>
