<?php

use BagistoPlus\Visual\Actions\Cart\AddProductToCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Shop\Http\Controllers\API\CartController;

beforeEach(function () {
    $this->cartApi = Mockery::mock(CartController::class);
    app()->instance(CartController::class, $this->cartApi);
    app()->instance('request', Request::create('/cart', 'POST'));
});

afterEach(function () {
    Mockery::close();
});

it('returns the success message when the product is added', function () {
    $this->cartApi
        ->shouldReceive('store')
        ->once()
        ->andReturn(new JsonResource([
            'data' => [],
            'message' => 'Item added to cart.',
        ]));

    $result = app(AddProductToCart::class)->execute(['product_id' => 1, 'quantity' => 1]);

    expect($result)->toBe([
        'success' => true,
        'message' => 'Item added to cart.',
        'redirect_url' => null,
    ]);
});

it('returns a failure without redirect url when inventory is insufficient', function () {
    $this->cartApi
        ->shouldReceive('store')
        ->once()
        ->andReturn(new JsonResponse([
            'message' => 'The requested quantity is not available.',
        ], 400));

    $result = app(AddProductToCart::class)->execute(['product_id' => 1, 'quantity' => 999999]);

    expect($result)->toBe([
        'success' => false,
        'message' => 'The requested quantity is not available.',
        'redirect_url' => null,
    ]);
});

it('returns a failure with the product redirect url on a generic error', function () {
    $this->cartApi
        ->shouldReceive('store')
        ->once()
        ->andReturn(new JsonResponse([
            'redirect_uri' => 'http://localhost/simple-product',
            'message' => 'This product is inactive.',
        ], 400));

    $result = app(AddProductToCart::class)->execute(['product_id' => 1, 'quantity' => 1]);

    expect($result)->toBe([
        'success' => false,
        'message' => 'This product is inactive.',
        'redirect_url' => 'http://localhost/simple-product',
    ]);
});
