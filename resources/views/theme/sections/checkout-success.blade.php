<div class="bg-background min-h-screen py-12">
  <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
    <div class="mb-12 text-center">
      <div class="bg-success/10 mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full">
        <x-lucide-check-circle class="text-success h-8 w-8" />
      </div>
      <h1 class="text-secondary mb-4 text-3xl">
        @lang('shop::app.checkout.success.thanks')
      </h1>

      <p class="text-secondary">
        @if (!empty($order->checkout_message))
          {!! nl2br($order->checkout_message) !!}
        @else
          @lang('shop::app.checkout.success.info')
        @endif
      </p>
    </div>

    <div class="bg-surface-alt mb-8 overflow-hidden rounded-lg shadow-sm">
      <div class="border-b border-neutral-200 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-secondary mb-1 text-sm">Order number</p>
            <p class="text-secondary font-medium">#{{ $order->increment_id }}</p>
          </div>

          <button class="text-primary inline-flex items-center hover:opacity-80">
            <x-lucide-download class="mr-2 h-5 w-5" />
            Download Invoice
          </button>
        </div>
      </div>

      <div class="space-y-4 border-b border-gray-200 p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <div class="text-secondary mb-1 flex items-center gap-2">
              <x-lucide-calendar class="h-5 w-5" />
              Order Date
            </div>
            <p class="text-secondary">
              {{ $order->created_at->format('F j, Y') }}
            </p>
          </div>
          <div>
            <div class="text-secondary mb-1 flex items-center gap-2">
              <x-lucide-clock class="h-5 w-5" />
              Order Time
            </div>
            <p class="text-secondary">
              {{ $order->created_at->format('h:i A') }}
            </p>
          </div>
        </div>
        <div>
          <div class="text-secondary mb-1 flex items-center gap-2">
            <x-lucide-truck class="h-5 w-5" />
            Confirmation sent to
          </div>
          <p class="text-secondary">
            {{ $order->customer_email }}
          </p>
        </div>
      </div>

      <div class="space-y-6 p-6">
        <div class="space-y-4">
          <div class="grid gap-4">
            @foreach ($order->items as $item)
              <div class="flex gap-4">
                <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100">
                  <img
                    src="{{ $item->product->base_image_url }}"
                    alt="{{ $item->name }}"
                    class="h-full w-full object-cover"
                  >
                </div>
                <div class="flex-1">
                  <h3 class="text-secondary font-medium">{{ $item->name }}</h3>
                  <p class="text-secondary text-sm">Quantity: {{ $item->qty_ordered }}</p>
                  <p class="text-primary text-sm">
                    <x-shop::formatted-price :price="$item->total_incl_tax" />
                  </p>
                </div>
              </div>
            @endforeach
          </div>

          <div class="border-t border-gray-200 pt-4">
            <div class="space-y-2">
              <div class="text-secondary flex justify-between">
                <span>Subtotal</span>
                <span><x-shop::formatted-price :price="$order->sub_total_incl_tax" /></span>
              </div>
              <div class="text-secondary flex justify-between">
                <span>Shipping</span>
                <span><x-shop::formatted-price :price="$order->shipping_amount_incl_tax" /></span>
              </div>
              <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-medium">
                <span>Total</span>
                <span class="text-primary">
                  <x-shop::formatted-price :price="$order->grand_total" />
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col justify-center gap-4 pb-4 lg:flex-row">
        <a class="bg-primary inline-flex items-center justify-center rounded-full px-6 py-3 text-white transition-opacity hover:opacity-90" href="{{ route('shop.home.index') }}">
          Continue Shopping
          <x-lucide-arrow-right class="ml-2 h-5 w-5" />
        </a>

        @auth('customer')
          <a class="border-primary text-primary hover:bg-primary inline-flex items-center justify-center rounded-full border px-6 py-3 transition-colors hover:text-white"
            href="{{ route('shop.customers.account.orders.view', $order->id) }}"
          >
            View Order
            <x-lucide-arrow-right class="ml-2 h-5 w-5" />
          </a>
        @endauth
      </div>
    </div>
  </div>
