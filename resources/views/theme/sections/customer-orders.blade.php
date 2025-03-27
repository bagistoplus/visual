<div class="rounded-lg bg-white shadow-sm">
  <div class="border-b border-neutral-200 p-4">
    <div class="flex items-center justify-between">
      <h1 class="text-secondary font-serif text-2xl">My Orders</h1>
    </div>
  </div>

  <x-shop::datagrid :src="route('shop.customers.account.orders.index')">
    {{-- <x-slot:mobile>
      hey
    </x-slot:mobile> --}}
  </x-shop::datagrid>
</div>
