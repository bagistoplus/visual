<div class="" x-data>
  <!-- Order Title & Status -->
  <div class="flex items-center">
    <h1 class="mr-6 text-2xl font-semibold text-neutral-800">
      @lang('shop::app.customers.account.orders.view.page-title', ['order_id' => $order->increment_id])
    </h1>

    <span class="{{ 'label-' . $order->status }}">
      @lang('shop::app.customers.account.orders.status.options.' . $order->status)
    </span>

    <div class="ml-auto hidden gap-2 lg:flex">
      @if ($order->canReorder() && core()->getConfigData('sales.order_settings.reorder.shop'))
        <x-shop::ui.button
          variant="outline"
          color="primary"
          size="sm"
          href="{{ route('shop.customers.account.orders.reorder', $order->id) }}"
        >
          @lang('shop::app.customers.account.orders.view.reorder-btn-title')
        </x-shop::ui.button>
      @endif

      @if ($order->canCancel())
        <form
          x-ref="cancelForm"
          method="post"
          action="{{ route('shop.customers.account.orders.cancel', $order->id) }}"
        >
          @csrf
          <x-shop::ui.button
            size="sm"
            variant="outline"
            color="danger"
            type="submit"
            x-on:click.prevent="$confirm(() => $refs.cancelForm.submit())"
          >
            @lang('shop::app.customers.account.orders.view.cancel-btn-title')
          </x-shop::ui.button>
        </form>
      @endif
    </div>
  </div>

  <!-- Tabs -->
  @php
    $tabs = [
        ['name' => 'information', 'title' => trans('shop::app.customers.account.orders.view.information.info'), 'enabled' => true],
        ['name' => 'invoices', 'title' => trans('shop::app.customers.account.orders.view.invoices.invoices'), 'enabled' => $order->invoices->isNotEmpty()],
        ['name' => 'shipments', 'title' => trans('shop::app.customers.account.orders.view.shipments.shipments'), 'enabled' => $order->shipments->isNotEmpty()],
        ['name' => 'refunds', 'title' => trans('shop::app.customers.account.orders.view.refunds.refunds'), 'enabled' => $order->refunds->isNotEmpty()],
    ];
  @endphp
  <div class="mb-8" x-data="{ activeTab: 'information' }">
    <div class="mb-6 flex border-b-2 border-neutral-100 max-sm:overflow-x-auto">
      @foreach (collect($tabs)->where('enabled', true) as $tab)
        <button
          class="hover:text-primary cursor-pointer px-5 py-3 font-medium"
          x-bind:class="{ 'border-b-4 border-primary text-primary -mb-px font-semibold': activeTab === '{{ $tab['name'] }}' }"
          x-on:click="activeTab = '{{ $tab['name'] }}'"
        >
          {{ $tab['title'] }}
        </button>
      @endforeach
    </div>

    <!-- Information Tab -->
    <div id="information" x-show="activeTab === 'information'">
      <div class="my-4 flex gap-4 sm:hidden">
        @if ($order->canReorder() && core()->getConfigData('sales.order_settings.reorder.shop'))
          <x-shop::ui.button
            variant="outline"
            color="primary"
            size="sm"
            href="{{ route('shop.customers.account.orders.reorder', $order->id) }}"
            class="flex-1"
          >
            @lang('shop::app.customers.account.orders.view.reorder-btn-title')
          </x-shop::ui.button>
        @endif

        @if ($order->canCancel())
          <form
            x-ref="cancelForm"
            method="post"
            action="{{ route('shop.customers.account.orders.cancel', $order->id) }}"
          >
            @csrf
            <x-shop::ui.button
              size="sm"
              variant="outline"
              color="danger"
              type="submit"
              class="flex-1"
              x-on:click.prevent="$confirm(() => $refs.cancelForm.submit())"
            >
              @lang('shop::app.customers.account.orders.view.cancel-btn-title')
            </x-shop::ui.button>
          </form>
        @endif
      </div>

      <div class="bg-background mb-6 rounded-lg p-6 shadow">
        <h2 class="mb-5 text-lg font-semibold text-neutral-700">
          @lang('shop::app.customers.account.orders.view.information.placed-on')
          {{ core()->formatDate($order->created_at, 'd M Y') }}
        </h2>

        <div class="space-y-6">
          @foreach ($order->items as $item)
            @php
              $image = product_image()->getProductBaseImage($item->product);
            @endphp

            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center">
              <img
                src="{{ $image['small_image_url'] }}"
                alt="Product 2"
                class="h-16 w-16 flex-none rounded object-cover"
              >
              <div class="flex-1">
                <h4 class="text-base font-medium text-neutral-700">
                  {{ $item->name }}
                </h4>

                <p class="text-sm">SKU: {{ $item->getTypeInstance()->getOrderedItem($item)->sku }}</p>
                <p class="mt-1 text-sm">
                  <span>Status:</span>
                  @if ($item->qty_ordered)
                    <span>@lang('shop::app.customers.account.orders.view.information.ordered-item', ['qty_ordered' => $item->qty_ordered])</span>
                  @endif

                  @if ($item->qty_invoiced)
                    <span>, @lang('shop::app.customers.account.orders.view.information.invoiced-item', ['qty_invoiced' => $item->qty_invoiced])</span>
                  @endif

                  @if ($item->qty_shipped)
                    <span>, @lang('shop::app.customers.account.orders.view.information.item-shipped', ['qty_shipped' => $item->qty_shipped])</span>
                  @endif

                  @if ($item->qty_refunded)
                    <span>, @lang('shop::app.customers.account.orders.view.information.item-refunded', ['qty_refunded' => $item->qty_refunded])</span>
                  @endif

                  @if ($item->qty_canceled)
                    <span>, @lang('shop::app.customers.account.orders.view.information.item-canceled', ['qty_canceled' => $item->qty_canceled])</span>
                  @endif
                </p>
              </div>

              <div class="flex-none sm:text-right">
                @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                  <div class="text-sm font-medium">
                    Price: {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }}
                  </div>
                  <div class="text-sm">
                    Subtotal: {{ core()->formatBasePrice($item->total_incl_tax) }}
                  </div>
                @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                  <div class="text-sm font-medium text-neutral-700">
                    Price: {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }}
                  </div>
                  <div class="text-xs font-medium">
                    Price exlc. tax: {{ core()->formatPrice($item->price, $order->order_currency_code) }}
                  </div>

                  <div class="mt-2 text-sm text-neutral-700">
                    Subtotal: {{ core()->formatBasePrice($item->total_incl_tax) }}
                  </div>
                  <div class="text-xs font-medium">
                    Subtotal exlc. tax: {{ core()->formatPrice($item->total, $order->order_currency_code) }}
                  </div>
                @else
                  <div class="text-sm font-medium">
                    Price: {{ core()->formatPrice($item->price, $order->order_currency_code) }}
                  </div>
                  <div class="text-sm font-medium">
                    Subtotal: {{ core()->formatPrice($item->total, $order->order_currency_code) }}
                  </div>
                @endif

              </div>
            </div>

            @if (!$loop->last)
              <hr class="my-4 border-t border-neutral-200" />
            @endif
          @endforeach
        </div>
      </div>

      <!-- Order Summary -->
      <div class="bg-background rounded-lg p-6 shadow">
        <h2 class="mb-5 text-lg font-semibold text-neutral-700">Order Summary</h2>
        <div class="flex flex-col gap-3">
          <!-- subtotal -->
          <div class="flex justify-between text-neutral-600">
            <span>
              @lang('shop::app.customers.account.orders.view.information.subtotal')
            </span>
            <span class="font-medium text-neutral-800">
              {{ core()->formatPrice($order->sub_total, $order->order_currency_code) }}
            </span>
          </div>

          <!-- shipping -->
          @if ($order->haveStockableItems())
            <div class="flex justify-between text-neutral-600">
              <span>
                @lang('shop::app.customers.account.orders.view.information.shipping-handling')
              </span>
              <span class="font-medium text-neutral-800">
                {{ core()->formatPrice($order->shipping_amount, $order->order_currency_code) }}
              </span>
            </div>
          @endif

          <!-- Tax -->
          <div class="flex justify-between text-neutral-600">
            <span>
              @lang('shop::app.customers.account.orders.view.information.tax')
            </span>
            <span class="font-medium text-neutral-800">
              {{ core()->formatPrice($order->tax_amount, $order->order_currency_code) }}
            </span>
          </div>

          <!-- Discount -->
          @if ($order->base_discount_amount > 0)
            <div class="flex justify-between text-neutral-600">
              <span>
                @lang('shop::app.customers.account.orders.view.information.discount')
                @if ($order->coupon_code)
                  ({{ $order->coupon_code }})
                @endif
              </span>
              <span class="font-medium text-neutral-800">
                {{ core()->formatPrice($order->discount_amount, $order->order_currency_code) }}
              </span>
            </div>
          @endif

          <div class="mt-3 flex justify-between border-t pt-3 text-lg font-semibold text-neutral-800">
            <span>
              @lang('shop::app.customers.account.orders.view.information.grand-total')
            </span>
            <span>
              {{ core()->formatPrice($order->grand_total, $order->order_currency_code) }}
            </span>
          </div>

          <!-- Total paid -->
          <div class="flex justify-between text-neutral-600">
            <span>
              @lang('shop::app.customers.account.orders.view.information.total-paid')
            </span>
            <span class="font-medium text-neutral-800">
              {{ core()->formatPrice($order->grand_total_invoiced, $order->order_currency_code) }}
            </span>
          </div>

          <!-- Total Refunded -->
          <div class="flex justify-between text-neutral-600">
            <span>
              @lang('shop::app.customers.account.orders.view.information.total-refunded')
            </span>
            <span class="font-medium text-neutral-800">
              {{ core()->formatPrice($order->grand_total_refunded, $order->order_currency_code) }}
            </span>
          </div>

          <!-- Total Due -->
          <div class="flex justify-between text-neutral-600">
            <span>
              @lang('shop::app.customers.account.orders.view.information.total-due')
            </span>
            <span class="font-medium text-neutral-800">
              @if ($order->status !== \Webkul\Sales\Models\Order::STATUS_CANCELED)
                {{ core()->formatPrice($order->total_due, $order->order_currency_code) }}
              @else
                {{ core()->formatPrice(0.0, $order->order_currency_code) }}
              @endif
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Invoices Tab -->
    @if ($order->invoices->count())
      <div id="invoices" x-show="activeTab === 'invoices'">
        @foreach ($order->invoices as $invoice)
          <div class="bg-background mb-6 overflow-hidden rounded-lg shadow">
            <div class="flex items-center justify-between gap-4 border-b border-neutral-100 px-4 py-3">
              <h3 class="text-base font-semibold text-neutral-600">
                @lang('shop::app.customers.account.orders.view.invoices.individual-invoice', ['invoice_id' => $invoice->increment_id ?? $invoice->id])
              </h3>

              <x-shop::ui.button
                size="sm"
                variant="soft"
                color="secondary"
                icon="lucide-download"
                href="{{ route('shop.customers.account.orders.print-invoice', $invoice->id) }}"
              >
                @lang('shop::app.customers.account.orders.view.invoices.print')
              </x-shop::ui.button>
            </div>

            <div>

              <!-- Mobile Card View -->
              <div class="sm:hidden">
                @foreach ($invoice->items as $item)
                  <div class="p-4">
                    <p class="mb-2 font-medium text-neutral-600">
                      {{ $item->name }}
                    </p>
                    <div class="flex justify-between">
                      <p class="text-sm text-neutral-600">
                        @lang('shop::app.customers.account.orders.view.invoices.sku'):
                      </p>
                      <p class="text-right text-sm">
                        {{ $item->getTypeInstance()->getOrderedItem($item)->sku }}
                      </p>
                    </div>
                    <div class="flex justify-between">
                      <p class="text-sm text-neutral-600">
                        @lang('shop::app.customers.account.orders.view.invoices.price'):
                      </p>
                      <p class="text-right text-sm">
                        @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                          {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }}
                        @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                          <p>
                            {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }}
                          </p>

                          <p class="whitespace-nowrap text-xs font-normal">
                            @lang('shop::app.customers.account.orders.view.information.excl-tax')

                            <span class="font-medium">
                              {{ core()->formatPrice($item->price, $order->order_currency_code) }}
                            </span>
                          </p>
                        @else
                          {{ core()->formatPrice($item->price, $order->order_currency_code) }}
                        @endif
                      </p>
                    </div>
                  </div>
                @endforeach
              </div>

              <!-- Desktop Table View -->
              @php
                $cols = [
                    trans('shop::app.customers.account.orders.view.invoices.sku'),
                    trans('shop::app.customers.account.orders.view.invoices.product-name'),
                    trans('shop::app.customers.account.orders.view.invoices.qty'),
                    trans('shop::app.customers.account.orders.view.invoices.price'),
                    trans('shop::app.customers.account.orders.view.invoices.subtotal'),
                ];
              @endphp
              <div class="hidden overflow-x-auto sm:block">
                <table class="min-w-full text-left text-sm">
                  <thead>
                    <tr class="bg-surface-alt border-b border-neutral-100">
                      @foreach ($cols as $col)
                        <th class="cursor-pointer px-4 py-3 text-left align-middle text-sm font-medium text-neutral-700">
                          {{ $col }}
                        </th>
                      @endforeach
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-neutral-200">
                    @foreach ($invoice->items as $item)
                      <tr class="hover:bg-neutral-50 max-sm:mb-4 max-sm:block max-sm:rounded max-sm:border">
                        <td class="px-4 py-3">
                          {{ $item->getTypeInstance()->getOrderedItem($item)->sku }}
                        </td>
                        <td class="px-4 py-3">
                          {{ $item->name }}
                        </td>
                        <td class="px-4 py-3">
                          {{ $item->qty }}
                        </td>
                        <td class="px-4 py-3">
                          @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                            {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }}
                          @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                            {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }}

                            <span class="whitespace-nowrap text-xs font-normal">
                              @lang('shop::app.customers.account.orders.view.information.excl-tax')

                              <span class="font-medium">
                                {{ core()->formatPrice($item->price, $order->order_currency_code) }}
                              </span>
                            </span>
                          @else
                            {{ core()->formatPrice($item->price, $order->order_currency_code) }}
                          @endif
                        </td>
                        <td class="px-4 py-3">
                          @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                            {{ core()->formatPrice($item->total_incl_tax, $order->order_currency_code) }}
                          @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                            {{ core()->formatPrice($item->total_incl_tax, $order->order_currency_code) }}

                            <span class="whitespace-nowrap text-xs font-normal">
                              @lang('shop::app.customers.account.orders.view.invoices.excl-tax')

                              <span class="font-medium">
                                {{ core()->formatPrice($item->total, $order->order_currency_code) }}
                              </span>
                            </span>
                          @else
                            {{ core()->formatPrice($item->total, $order->order_currency_code) }}
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            <div class="mt-4 sm:mt-6">
              <div class="bg-surface-alt border-y px-4 py-2 text-neutral-700">
                @lang('Order Summary')
              </div>
              <div class="px-4 py-2 text-sm">
                <!-- subtotal -->
                <div class="flex justify-between text-neutral-600">
                  <span>
                    @lang('shop::app.customers.account.orders.view.invoices.subtotal')
                  </span>
                  <span class="font-medium text-neutral-800">
                    {{ core()->formatPrice($invoice->sub_total, $order->order_currency_code) }}
                  </span>
                </div>

                <!-- shipping -->
                @if ($order->haveStockableItems())
                  <div class="flex justify-between text-neutral-600">
                    <span>
                      @lang('shop::app.customers.account.orders.view.invoices.shipping-handling')
                    </span>
                    <span class="font-medium text-neutral-800">
                      {{ core()->formatPrice($invoice->shipping_amount, $order->order_currency_code) }}
                    </span>
                  </div>
                @endif

                <!-- Discount -->
                @if ($invoice->base_discount_amount > 0)
                  <div class="flex justify-between text-neutral-600">
                    <span>
                      @lang('shop::app.customers.account.orders.view.invoices.discount')
                    </span>
                    <span class="font-medium text-neutral-800">
                      {{ core()->formatPrice($invoice->discount_amount, $order->order_currency_code) }}
                    </span>
                  </div>
                @endif

                <!-- Tax -->
                <div class="flex justify-between text-neutral-600">
                  <span>
                    @lang('shop::app.customers.account.orders.view.invoices.tax')
                  </span>
                  <span class="font-medium text-neutral-800">
                    {{ core()->formatPrice($invoice->tax_amount, $order->order_currency_code) }}
                  </span>
                </div>

                <div class="mt-1 flex justify-between border-t pt-1 font-semibold text-neutral-800">
                  <span>
                    @lang('shop::app.customers.account.orders.view.invoices.grand-total')
                  </span>
                  <span>
                    {{ core()->formatPrice($invoice->grand_total, $order->order_currency_code) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <!-- Shipments Tab -->
    @if ($order->shipments->count())
      <div id="shipments" x-show="activeTab === 'shipments'">
        @foreach ($order->shipments as $shipment)
          <div class="bg-background mb-6 overflow-hidden rounded-lg shadow">
            <div class="flex items-center justify-between gap-4 border-b border-neutral-100 px-4 py-3">
              <h3 class="text-base font-semibold text-neutral-600">
                @lang('shop::app.customers.account.orders.view.shipments.tracking-number'):
                {{ $shipment->track_number }}
              </h3>

              <span>
                @lang('shop::app.customers.account.orders.view.shipments.individual-shipment', ['shipment_id' => $shipment->id])
              </span>
            </div>

            <div>
              <!-- Mobile Card View -->
              <div class="sm:hidden">
                @foreach ($shipment->items as $item)
                  <div class="p-4">
                    <p class="mb-2 font-medium text-neutral-600">
                      {{ $item->name }}
                    </p>
                    <div class="flex justify-between">
                      <p class="text-sm text-neutral-600">
                        @lang('shop::app.customers.account.orders.view.shipments.sku'):
                      </p>
                      <p class="text-right text-sm">
                        {{ $item->sku }}
                      </p>
                    </div>
                    <div class="flex justify-between">
                      <p class="text-sm text-neutral-600">
                        @lang('shop::app.customers.account.orders.view.shipments.qty'):
                      </p>
                      <p class="text-right text-sm">
                        {{ $item->qty }}
                      </p>
                    </div>
                  </div>
                @endforeach
              </div>

              <!-- Desktop Table View -->
              @php
                $cols = [
                    trans('shop::app.customers.account.orders.view.shipments.sku'),
                    trans('shop::app.customers.account.orders.view.shipments.product-name'),
                    trans('shop::app.customers.account.orders.view.shipments.qty'),
                ];
              @endphp
              <div class="hidden overflow-x-auto sm:block">
                <table class="min-w-full text-left text-sm">
                  <thead>
                    <tr class="bg-surface-alt border-b border-neutral-100">
                      @foreach ($cols as $col)
                        <th class="cursor-pointer px-4 py-3 text-left align-middle text-sm font-medium text-neutral-700">
                          {{ $col }}
                        </th>
                      @endforeach
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-neutral-200">
                    @foreach ($shipment->items as $item)
                      <tr class="hover:bg-neutral-50 max-sm:mb-4 max-sm:block max-sm:rounded max-sm:border">
                        <td class="px-4 py-3">
                          {{ $item->sku }}
                        </td>
                        <td class="px-4 py-3">
                          {{ $item->name }}
                        </td>
                        <td class="px-4 py-3">
                          {{ $item->qty }}
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <!-- Refunds Tab -->
    @if ($order->refunds->count())
      <div id="refunds" x-show="activeTab === 'refunds'">
        @foreach ($order->refunds as $refund)
          <div class="bg-background mb-6 overflow-hidden rounded-lg shadow">
            <div class="flex items-center justify-between gap-4 border-b border-neutral-100 px-4 py-3">
              <h3 class="text-base font-semibold text-neutral-600">
                @lang('shop::app.customers.account.orders.view.refunds.individual-refund', ['refund_id' => $refund->id])
              </h3>
            </div>

            <div>
              <!-- Mobile Card View -->
              <div class="sm:hidden">
                @foreach ($refund->items as $item)
                  <div class="p-4">
                    <p class="mb-2 font-medium text-neutral-600">
                      {{ $item->name }}
                    </p>
                    <div class="flex justify-between">
                      <p class="text-sm text-neutral-600">
                        @lang('shop::app.customers.account.orders.view.refunds.sku'):
                      </p>
                      <p class="text-right text-sm">
                        {{ $item->child ? $item->child->sku : $item->sku }}
                      </p>
                    </div>
                    <div class="flex justify-between">
                      <p class="text-sm text-neutral-600">
                        @lang('shop::app.customers.account.orders.view.refunds.qty'):
                      </p>
                      <p class="text-right text-sm">
                        {{ $item->qty }}
                      </p>
                    </div>
                    <div class="flex justify-between">
                      <p class="text-sm text-neutral-600">
                        @lang('shop::app.customers.account.orders.view.refunds.price'):
                      </p>
                      <p class="text-right text-sm">
                        @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                          {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }}
                        @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                          <p>
                            {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }}
                          </p>

                          <p class="whitespace-nowrap text-xs font-normal">
                            @lang('shop::app.customers.account.orders.view.information.excl-tax')

                            <span class="font-medium">
                              {{ core()->formatPrice($item->price, $order->order_currency_code) }}
                            </span>
                          </p>
                        @else
                          {{ core()->formatPrice($item->price, $order->order_currency_code) }}
                        @endif
                      </p>
                    </div>
                    <div class="flex justify-between">
                      <p class="text-sm text-neutral-600">
                        @lang('shop::app.customers.account.orders.view.refunds.subtotal'):
                      </p>
                      <p class="text-right text-sm">
                        @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                          {{ core()->formatPrice($item->total_incl_tax, $order->order_currency_code) }}
                        @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                          <p>
                            {{ core()->formatPrice($item->total_incl_tax, $order->order_currency_code) }}
                          </p>

                          <p class="whitespace-nowrap text-xs font-normal">
                            @lang('shop::app.customers.account.orders.view.information.excl-tax')

                            <span class="font-medium">
                              {{ core()->formatPrice($item->total, $order->order_currency_code) }}
                            </span>
                          </p>
                        @else
                          {{ core()->formatPrice($item->total, $order->order_currency_code) }}
                        @endif
                      </p>
                    </div>
                  </div>
                @endforeach
              </div>

              <!-- Desktop Table View -->
              @php
                $cols = [
                    trans('shop::app.customers.account.orders.view.refunds.sku'),
                    trans('shop::app.customers.account.orders.view.refunds.product-name'),
                    trans('shop::app.customers.account.orders.view.refunds.qty'),
                    trans('shop::app.customers.account.orders.view.refunds.price'),
                    trans('shop::app.customers.account.orders.view.refunds.subtotal'),
                ];
              @endphp
              <div class="hidden overflow-x-auto sm:block">
                <table class="min-w-full text-left text-sm">
                  <thead>
                    <tr class="bg-surface-alt border-b border-neutral-100">
                      @foreach ($cols as $col)
                        <th class="cursor-pointer px-4 py-3 text-left align-middle text-sm font-medium text-neutral-700">
                          {{ $col }}
                        </th>
                      @endforeach
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-neutral-200">
                    @foreach ($refund->items as $item)
                      <tr class="hover:bg-neutral-50 max-sm:mb-4 max-sm:block max-sm:rounded max-sm:border">
                        <td class="px-4 py-3">
                          {{ $item->child ? $item->child->sku : $item->sku }}
                        </td>
                        <td class="px-4 py-3">
                          {{ $item->name }}
                        </td>
                        <td class="px-4 py-3">
                          {{ $item->qty }}
                        </td>
                        <td class="px-4 py-3">
                          @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                            {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }}
                          @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                            {{ core()->formatPrice($item->price_incl_tax, $order->order_currency_code) }}

                            <span class="whitespace-nowrap text-xs font-normal">
                              @lang('shop::app.customers.account.orders.view.information.excl-tax')

                              <span class="font-medium">
                                {{ core()->formatPrice($item->price, $order->order_currency_code) }}
                              </span>
                            </span>
                          @else
                            {{ core()->formatPrice($item->price, $order->order_currency_code) }}
                          @endif
                        </td>
                        <td class="px-4 py-3">
                          @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                            {{ core()->formatPrice($item->total_incl_tax, $order->order_currency_code) }}
                          @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                            {{ core()->formatPrice($item->total_incl_tax, $order->order_currency_code) }}

                            <span class="whitespace-nowrap text-xs font-normal">
                              @lang('shop::app.customers.account.orders.view.information.excl-tax')

                              <span class="font-medium">
                                {{ core()->formatPrice($item->total, $order->order_currency_code) }}
                              </span>
                            </span>
                          @else
                            {{ core()->formatPrice($item->total, $order->order_currency_code) }}
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            <div class="mt-4 sm:mt-6">
              <div class="bg-surface-alt border-y px-4 py-2 text-neutral-700">
                @lang('shop::app.customers.account.orders.view.refunds.order-summary')
              </div>
              <div class="px-4 py-2 text-sm">
                <!-- subtotal -->
                <div class="flex justify-between text-neutral-600">
                  <span>
                    @lang('shop::app.customers.account.orders.view.refunds.subtotal')
                  </span>
                  <span class="font-medium text-neutral-800">
                    {{ core()->formatPrice($refund->sub_total, $order->order_currency_code) }}
                  </span>
                </div>

                <!-- shipping -->
                @if ($order->haveStockableItems())
                  <div class="flex justify-between text-neutral-600">
                    <span>
                      @lang('shop::app.customers.account.orders.view.refunds.shipping-handling')
                    </span>
                    <span class="font-medium text-neutral-800">
                      {{ core()->formatPrice($refund->shipping_amount, $order->order_currency_code) }}
                    </span>
                  </div>
                @endif

                <!-- Discount -->
                @if ($refund->discount_amount > 0)
                  <div class="flex justify-between text-neutral-600">
                    <span>
                      @lang('shop::app.customers.account.orders.view.refunds.discount')
                    </span>
                    <span class="font-medium text-neutral-800">
                      {{ core()->formatPrice($order->discount_amount, $order->order_currency_code) }}
                    </span>
                  </div>
                @endif

                <!-- Tax -->
                @if ($refund->tax_amount > 0)
                  <div class="flex justify-between text-neutral-600">
                    <span>
                      @lang('shop::app.customers.account.orders.view.refunds.tax')
                    </span>
                    <span class="font-medium text-neutral-800">
                      {{ core()->formatPrice($refund->tax_amount, $order->order_currency_code) }}
                    </span>
                  </div>
                @endif

                <!-- Adjustments Refund -->
                <div class="flex justify-between text-neutral-600">
                  <span>
                    @lang('shop::app.customers.account.orders.view.refunds.adjustment-refund')
                  </span>
                  <span class="font-medium text-neutral-800">
                    {{ core()->formatPrice($refund->adjustment_refund, $order->order_currency_code) }}
                  </span>
                </div>

                <!-- Adjustment fee -->
                <div class="flex justify-between text-neutral-600">
                  <span>
                    @lang('shop::app.customers.account.orders.view.refunds.adjustment-fee')
                  </span>
                  <span class="font-medium text-neutral-800">
                    {{ core()->formatPrice($refund->adjustment_fee, $order->order_currency_code) }}
                  </span>
                </div>

                <!-- Grand Total -->
                <div class="mt-1 flex justify-between border-t pt-1 font-semibold text-neutral-800">
                  <span>
                    @lang('shop::app.customers.account.orders.view.refunds.grand-total')
                  </span>
                  <span>
                    {{ core()->formatPrice($invoice->grand_total, $order->order_currency_code) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  <!-- Shipping Address and Payment methods -->
  <div class="bg-background mt-6 rounded-lg p-4 shadow">
    <div class="flex flex-wrap gap-6">
      <!-- Billing Address -->
      @if ($order->billing_address)
        <div class="bg-surface-alt w-full rounded p-4 sm:max-w-64">
          <div class="mb-3 font-semibold">@lang('shop::app.customers.account.orders.view.billing-address')</div>
          <div class="leading-relaxed">
            <x-shop::order.address :address="$order->billing_address" />
          </div>
        </div>
      @endif

      <!-- Shipping Address -->
      @if ($order->shipping_address)
        <div class="bg-surface-alt w-full rounded p-4 sm:max-w-64">
          <div class="mb-3 font-semibold">@lang('shop::app.customers.account.orders.view.shipping-address')</div>
          <div class="leading-relaxed">
            <x-shop::order.address :address="$order->shipping_address" />
          </div>
        </div>
      @endif
      <div class="w-full space-y-6 sm:max-w-64">
        @if ($order->shipping_address)
          <div class="bg-surface-alt rounded p-4">
            <div class="mb-3 font-semibold">
              @lang('shop::app.customers.account.orders.view.shipping-method')
            </div>
            <div class="leading-relaxed">
              {{ $order->shipping_title }}
            </div>
          </div>
        @endif

        <div class="bg-surface-alt rounded p-4">
          <div class="mb-3 font-semibold">
            @lang('shop::app.customers.account.orders.view.payment-method')
          </div>
          <div class="leading-relaxed">
            {{ core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') }}

            @if (!empty($additionalDetails))
              <div class="instructions">
                <label>{{ $additionalDetails['title'] }}</label>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
