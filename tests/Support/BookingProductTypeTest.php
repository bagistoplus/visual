<?php

use BagistoPlus\Visual\Providers\CoreServiceProvider;
use BagistoPlus\Visual\Support\BookingProductType;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\BookingProduct\Helpers\Booking as BookingHelper;
use Webkul\BookingProduct\Repositories\BookingProductRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductAttributeValueRepository;
use Webkul\Product\Repositories\ProductCustomerGroupPriceRepository;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductVideoRepository;
use Webkul\Product\Type\Booking;

function bookingType(BookingProductRepository $bookingProducts): BookingProductType
{
    return new BookingProductType(
        Mockery::mock(CustomerRepository::class),
        Mockery::mock(AttributeRepository::class),
        Mockery::mock(ProductRepository::class),
        Mockery::mock(ProductAttributeValueRepository::class),
        Mockery::mock(ProductInventoryRepository::class),
        Mockery::mock(ProductImageRepository::class),
        Mockery::mock(ProductVideoRepository::class),
        Mockery::mock(ProductCustomerGroupPriceRepository::class),
        $bookingProducts,
        Mockery::mock(BookingHelper::class),
    );
}

afterEach(function () {
    Mockery::close();
});

// Bagisto asks three times per rendered card, and each ask eager loads the five
// slot relations.
it('looks a booking product up once however many times it is asked', function () {
    $booking = (object) ['id' => 1, 'type' => 'default'];

    $repository = Mockery::mock(BookingProductRepository::class);
    $repository->shouldReceive('findOneByField')
        ->once()
        ->with('product_id', 7)
        ->andReturn($booking);

    $type = bookingType($repository);

    expect($type->getBookingProduct(7))->toBe($booking)
        ->and($type->getBookingProduct(7))->toBe($booking)
        ->and($type->getBookingProduct(7))->toBe($booking);
});

it('looks up each product separately', function () {
    $repository = Mockery::mock(BookingProductRepository::class);
    $repository->shouldReceive('findOneByField')->once()->with('product_id', 7)->andReturn((object) ['id' => 1]);
    $repository->shouldReceive('findOneByField')->once()->with('product_id', 8)->andReturn((object) ['id' => 2]);

    $type = bookingType($repository);

    expect($type->getBookingProduct(7)->id)->toBe(1)
        ->and($type->getBookingProduct(8)->id)->toBe(2)
        ->and($type->getBookingProduct(7)->id)->toBe(1);
});

// A store with no booking product for the id must not be asked again either.
it('remembers that a product has no booking row', function () {
    $repository = Mockery::mock(BookingProductRepository::class);
    $repository->shouldReceive('findOneByField')->once()->with('product_id', 9)->andReturn(null);

    $type = bookingType($repository);

    expect($type->getBookingProduct(9))->toBeNull()
        ->and($type->getBookingProduct(9))->toBeNull();
});

function bootBookingProductType(): void
{
    $provider = app()->getProvider(CoreServiceProvider::class);
    $method = new ReflectionMethod($provider, 'bootBookingProductType');
    $method->setAccessible(true);
    $method->invoke($provider);
}

it('takes over the booking type bagisto ships', function () {
    config(['product_types.booking.class' => Booking::class]);

    bootBookingProductType();

    expect(config('product_types.booking.class'))->toBe(BookingProductType::class)
        ->and(get_parent_class(BookingProductType::class))->toBe(Booking::class);
});

// A store that already points the type somewhere else has made a choice.
it('leaves a type the store already replaced alone', function () {
    config(['product_types.booking.class' => 'App\\ProductTypes\\Booking']);

    bootBookingProductType();

    expect(config('product_types.booking.class'))->toBe('App\\ProductTypes\\Booking');
});
