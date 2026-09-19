<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Settings\Support\ProductListTransformer;
use BagistoPlus\Visual\Settings\Support\ProductTransformer;
use BagistoPlus\Visual\Support\ResolvedModelCache;
use Illuminate\Support\Collection;
use Webkul\Product\Repositories\ProductRepository;

beforeEach(function () {
    $this->repository = Mockery::mock(ProductRepository::class);
    app()->instance(ProductRepository::class, $this->repository);

    $this->expectProductQuery = function (array $ids, Collection $products) {
        $this->repository
            ->shouldReceive('with')
            ->once()
            ->andReturnSelf();

        $this->repository
            ->shouldReceive('findWhereIn')
            ->once()
            ->with('id', $ids)
            ->andReturn($products);
    };
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

// A card grid reads each of these, and reading one lazily costs a query per
// product, so dropping one from the list is a silent regression.
it('eager loads the relations a product card reads', function () {
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

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->andReturn(collect());

    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    (new ProductListTransformer)->transform([1]);
});

it('resolves IDs through the product repository', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];
    $b = (object) ['id' => 2, 'name' => 'B'];

    ($this->expectProductQuery)([1, 2], collect([$a, $b]));

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

    ($this->expectProductQuery)([3, 1, 2], collect([$a, $b, $c]));

    $transformer = new ProductListTransformer;
    $result = $transformer->transform([3, 1, 2]);

    expect($result->pluck('id')->all())->toBe([3, 1, 2]);
});

it('skips missing IDs', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];

    ($this->expectProductQuery)([1, 999], collect([$a]));

    $transformer = new ProductListTransformer;
    $result = $transformer->transform([1, 999]);

    expect($result->pluck('id')->all())->toBe([1]);
});

it('seeds the model cache for a later singular lookup', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];

    ($this->expectProductQuery)([1], collect([$a]));
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    (new ProductListTransformer)->transform([1]);

    expect(app(ResolvedModelCache::class)->rememberProduct(1, fn () => 'not reached'))
        ->toBe($a);
});

it('resolves a repeated id once across two lists', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];

    ($this->expectProductQuery)([1], collect([$a]));
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $first = (new ProductListTransformer)->transform([1]);
    $second = (new ProductListTransformer)->transform([1]);

    expect($second->first())->toBe($first->first());
});

// A relation costs a query per batch, not per model, so an id resolved earlier
// must drop out of the batch rather than be fetched beside it.
it('queries only the ids it has not already resolved', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];
    $b = (object) ['id' => 2, 'name' => 'B'];

    $this->repository->shouldReceive('with')->twice()->andReturnSelf();
    $this->repository->shouldReceive('findWhereIn')->once()->with('id', [1])->andReturn(collect([$a]));
    $this->repository->shouldReceive('findWhereIn')->once()->with('id', [2])->andReturn(collect([$b]));
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    (new ProductListTransformer)->transform([1]);

    expect((new ProductListTransformer)->transform([1, 2])->pluck('id')->all())->toBe([1, 2]);
});

it('preloads resolved models in design mode', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];

    ($this->expectProductQuery)([1], collect([$a]));

    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldReceive('preloadModel')
        ->once()
        ->with('products', $a);

    $transformer = new ProductListTransformer;
    $transformer->transform([1]);
});

it('does not preload models outside design mode', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];

    ($this->expectProductQuery)([1], collect([$a]));

    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);
    ThemeEditor::shouldNotReceive('preloadModel');

    $transformer = new ProductListTransformer;
    $transformer->transform([1]);
});

// Both transformers load a product the same way, which is what lets either one
// answer from what the other remembered.
it('shares one product with the singular transformer, list first', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];

    ($this->expectProductQuery)([1], collect([$a]));
    $this->repository->shouldNotReceive('find');
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $fromList = (new ProductListTransformer)->transform([1])->first();

    expect((new ProductTransformer)(1))->toBe($fromList);
});

it('shares one product with the singular transformer, singular first', function () {
    $a = (object) ['id' => 1, 'name' => 'A'];

    $this->repository->shouldReceive('with')->once()->andReturnSelf();
    $this->repository->shouldReceive('find')->once()->with(1)->andReturn($a);
    $this->repository->shouldNotReceive('findWhereIn');
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $single = (new ProductTransformer)(1);

    expect((new ProductListTransformer)->transform([1])->first())->toBe($single);
});
