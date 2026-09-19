<?php

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Settings\Support\ProductTransformer;
use Webkul\Product\Repositories\ProductRepository;

beforeEach(function () {
    $this->repository = Mockery::mock(ProductRepository::class);
    app()->instance(ProductRepository::class, $this->repository);

    $this->expectProductQuery = function (int $id, mixed $product) {
        $this->repository->shouldReceive('with')->once()->andReturnSelf();
        $this->repository->shouldReceive('find')->once()->with($id)->andReturn($product);
    };
});

afterEach(function () {
    Mockery::close();
});

it('returns null for null id without querying', function () {
    $this->repository->shouldNotReceive('with');
    $this->repository->shouldNotReceive('find');

    $transformer = new ProductTransformer;

    expect($transformer(null))->toBeNull();
});

it('resolves the product by id', function () {
    $model = (object) ['id' => 10];

    ($this->expectProductQuery)(10, $model);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $transformer = new ProductTransformer;

    expect($transformer(10))->toBe($model);
});

it('queries the same id once per request', function () {
    $model = (object) ['id' => 10];

    ($this->expectProductQuery)(10, $model);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $transformer = new ProductTransformer;

    expect($transformer(10))->toBe($model)
        ->and($transformer(10))->toBe($model);
});

it('queries a missing id once per request', function () {
    ($this->expectProductQuery)(404, null);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $transformer = new ProductTransformer;

    expect($transformer(404))->toBeNull()
        ->and($transformer(404))->toBeNull();
});

it('preloads the resolved model in design mode', function () {
    $model = (object) ['id' => 10];

    ($this->expectProductQuery)(10, $model);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldReceive('preloadModel')->once()->with('products', $model);

    $transformer = new ProductTransformer;
    $transformer(10);
});

it('does not preload a missing model in design mode', function () {
    ($this->expectProductQuery)(404, null);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldNotReceive('preloadModel');

    $transformer = new ProductTransformer;

    expect($transformer(404))->toBeNull();
});

// Pinned by literal so an edit to the constant is a decision, not a slip. The
// list transformer shares it, so both ends of the cache stay in step.
it('eager loads the relations a rendered product reads', function () {
    $this->repository
        ->shouldReceive('with')
        ->once()
        ->with([
            'attribute_family',
            'images',
            'attribute_values',
            'price_indices',
            'inventory_indices',
            'reviews',
        ])
        ->andReturnSelf();

    $this->repository->shouldReceive('find')->once()->andReturn(null);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    (new ProductTransformer)(10);
});
