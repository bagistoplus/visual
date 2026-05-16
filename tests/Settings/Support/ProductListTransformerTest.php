<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Settings\Support\ProductListTransformer;
use Illuminate\Support\Collection;
use Webkul\Product\Repositories\ProductRepository;

beforeEach(function () {
    $this->repository = Mockery::mock(ProductRepository::class);
    app()->instance(ProductRepository::class, $this->repository);
});

afterEach(function () {
    Mockery::close();
});

it('implements SettingTransformerInterface', function () {
    expect(ProductListTransformer::class)
        ->toImplement(SettingTransformerInterface::class);
});

it('returns empty collection for null value', function () {
    $transformer = new ProductListTransformer;

    expect($transformer->transform(null))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('returns empty collection for non-array value', function () {
    $transformer = new ProductListTransformer;

    expect($transformer->transform('not-an-array'))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('returns empty collection for empty array', function () {
    $transformer = new ProductListTransformer;

    expect($transformer->transform([]))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('resolves IDs through the product repository', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];
    $b = (object) ['id' => 2, 'name' => 'B'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->with('id', [1, 2])
        ->andReturn(collect([$a, $b]));

    $transformer = new ProductListTransformer;
    $result = $transformer->transform([1, 2]);

    expect($result)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);
    expect($result->pluck('id')->all())->toBe([1, 2]);
});

it('preserves stored ID order regardless of repository result order', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];
    $b = (object) ['id' => 2, 'name' => 'B'];
    $c = (object) ['id' => 3, 'name' => 'C'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->with('id', [3, 1, 2])
        ->andReturn(collect([$a, $b, $c]));

    $transformer = new ProductListTransformer;
    $result = $transformer->transform([3, 1, 2]);

    expect($result->pluck('id')->all())->toBe([3, 1, 2]);
});

it('skips missing IDs', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->with('id', [1, 999])
        ->andReturn(collect([$a]));

    $transformer = new ProductListTransformer;
    $result = $transformer->transform([1, 999]);

    expect($result->pluck('id')->all())->toBe([1]);
});

it('preloads resolved models in design mode', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->andReturn(collect([$a]));

    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldReceive('preloadModel')
        ->once()
        ->with('products', $a);

    $transformer = new ProductListTransformer;
    $transformer->transform([1]);
});

it('does not preload models outside design mode', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->andReturn(collect([$a]));

    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);
    ThemeEditor::shouldNotReceive('preloadModel');

    $transformer = new ProductListTransformer;
    $transformer->transform([1]);
});
