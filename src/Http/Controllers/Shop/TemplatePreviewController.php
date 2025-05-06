<?php

namespace BagistoPlus\Visual\Http\Controllers\Shop;

use BagistoPlus\Visual\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductImage;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;

class TemplatePreviewController extends Controller
{
    /**
     * Preview the checkout success page
     */
    public function checkoutSuccess()
    {
        $now = Carbon::now();

        // Generate a fake order instance without saving
        $order = Order::factory()->make([
            'id' => 1,
            'created_at' => $now,
        ]);

        $total = 0;
        $totalQty = 0;
        $items = [];

        foreach (range(1, rand(1, 3)) as $i) {
            // Create a fake product (not persisted)
            $product = Product::factory()->make();
            $image = new ProductImage([
                'path' => $this->getFakeProductImage(),
            ]);

            $product->setRelation('images', collect([$image]));

            $qty = rand(1, 5);
            $price = $product->price ?? rand(10, 200);
            $totalPrice = $qty * $price;

            // Create order item (not persisted)
            $item = OrderItem::factory()->make([
                'product_id' => $product->id ?? $i,
                'sku' => $product->sku,
                'name' => $product->name,
                'type' => $product->type ?? 'simple',
                'qty_ordered' => $qty,
                'price' => $price,
                'base_price' => $price,
                'total' => $totalPrice,
                'base_total' => $totalPrice,
                'total_incl_tax' => $totalPrice,
                'base_total_incl_tax' => $totalPrice,
                'tax_amount' => 0,
                'base_tax_amount' => 0,
                'created_at' => $now,
            ]);

            $item->setRelation('product', $product);

            $items[] = $item;

            $total += $totalPrice;
            $totalQty += $qty;
        }

        // Assign computed totals
        $order->total_item_count = count($items);
        $order->total_qty_ordered = $totalQty;
        $order->grand_total = $total;
        $order->base_grand_total = $total;
        $order->sub_total = $total;
        $order->base_sub_total = $total;

        $order->sub_total_incl_tax = $total;
        $order->base_sub_total_incl_tax = $total;
        $order->grand_total_incl_tax = $total;
        $order->base_grand_total_incl_tax = $total;
        $order->tax_amount = 0;
        $order->base_tax_amount = 0;

        $order->setRelation('items', collect($items));

        return view('shop::checkout.success', compact('order'));
    }

    protected function getFakeProductImage()
    {
        $path = 'product-fake.svg';

        // Ensure the file exists only once
        if (! Storage::disk('public')->exists($path)) {
            // Download and store the image once
            Storage::disk('public')->put(
                $path,
                file_get_contents('https://placehold.co/100x100?text=Fake+Product')
            );
        }

        return $path;
    }
}
