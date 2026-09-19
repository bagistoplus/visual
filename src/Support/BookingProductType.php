<?php

namespace BagistoPlus\Visual\Support;

use Webkul\Product\Type\Booking;

/**
 * Bagisto's Booking::getBookingProduct() keeps its memo in a local variable, so
 * the lookup runs again on every call, and each one eager loads the five slot
 * relations. A rendered card asks three times, through isSaleable(),
 * haveDiscount() and getPriceHtml().
 *
 * Remembering the answer for the request is correct on a Bagisto that carries
 * the upstream fix too, so this stays while older versions are supported.
 */
class BookingProductType extends Booking
{
    public function getBookingProduct(int $productId)
    {
        return app(ResolvedModelCache::class)->remember(
            "booking_products:{$productId}",
            fn () => parent::getBookingProduct($productId)
        );
    }
}
