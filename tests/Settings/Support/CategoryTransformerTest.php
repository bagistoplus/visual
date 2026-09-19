<?php

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Settings\Support\CategoryTransformer;
use Webkul\Category\Repositories\CategoryRepository;

beforeEach(function () {
    $this->repository = Mockery::mock(CategoryRepository::class);
    app()->instance(CategoryRepository::class, $this->repository);

    $this->expectCategoryQuery = function (int $id, mixed $category) {
        $this->repository->shouldReceive('withCount')->once()->with('products')->andReturnSelf();
        $this->repository->shouldReceive('find')->once()->with($id)->andReturn($category);
    };
});

afterEach(function () {
    Mockery::close();
});

it('returns null for null id without querying', function () {
    $this->repository->shouldNotReceive('withCount');
    $this->repository->shouldNotReceive('find');

    $transformer = new CategoryTransformer;

    expect($transformer(null))->toBeNull();
});

it('resolves the category by id', function () {
    $model = (object) ['id' => 10];

    ($this->expectCategoryQuery)(10, $model);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $transformer = new CategoryTransformer;

    expect($transformer(10))->toBe($model);
});

it('queries the same id once per request', function () {
    $model = (object) ['id' => 10];

    ($this->expectCategoryQuery)(10, $model);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $transformer = new CategoryTransformer;

    expect($transformer(10))->toBe($model)
        ->and($transformer(10))->toBe($model);
});

it('queries a missing id once per request', function () {
    ($this->expectCategoryQuery)(404, null);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $transformer = new CategoryTransformer;

    expect($transformer(404))->toBeNull()
        ->and($transformer(404))->toBeNull();
});

it('preloads the resolved model in design mode', function () {
    $model = (object) ['id' => 10];

    ($this->expectCategoryQuery)(10, $model);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldReceive('preloadModel')->once()->with('categories', $model);

    $transformer = new CategoryTransformer;
    $transformer(10);
});

it('does not preload a missing model in design mode', function () {
    ($this->expectCategoryQuery)(404, null);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldNotReceive('preloadModel');

    $transformer = new CategoryTransformer;

    expect($transformer(404))->toBeNull();
});

// The list transformer counts products, so the singular one must too, or a
// category it remembers renders a count of zero wherever the list reads it.
it('counts products so a remembered category carries the count', function () {
    $this->repository->shouldReceive('withCount')->once()->with('products')->andReturnSelf();
    $this->repository->shouldReceive('find')->once()->andReturn(null);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    (new CategoryTransformer)(10);
});
