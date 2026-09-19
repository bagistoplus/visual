<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Settings\Support\CategoryListTransformer;
use BagistoPlus\Visual\Support\ResolvedModelCache;
use Illuminate\Support\Collection;
use Webkul\Category\Repositories\CategoryRepository;

beforeEach(function () {
    $this->repository = Mockery::mock(CategoryRepository::class);
    app()->instance(CategoryRepository::class, $this->repository);

    $this->expectCategoryQuery = function (array $ids, Collection $categories) {
        $this->repository
            ->shouldReceive('withCount')
            ->once()
            ->with('products')
            ->andReturnSelf();

        $this->repository
            ->shouldReceive('findWhereIn')
            ->once()
            ->with('id', $ids)
            ->andReturn($categories);
    };
});

afterEach(function () {
    Mockery::close();
});

it('implements SettingTransformerInterface', function () {
    expect(CategoryListTransformer::class)
        ->toImplement(SettingTransformerInterface::class);
});

it('returns empty collection for null value', function () {
    $transformer = new CategoryListTransformer;

    expect($transformer->transform(null))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('returns empty collection for non-array value', function () {
    $transformer = new CategoryListTransformer;

    expect($transformer->transform('nope'))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('returns empty collection for empty array', function () {
    $transformer = new CategoryListTransformer;

    expect($transformer->transform([]))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('resolves IDs through the category repository', function () {
    $a = (object) ['id' => 10, 'name' => 'A', 'products_count' => 3];
    $b = (object) ['id' => 20, 'name' => 'B', 'products_count' => 5];

    ($this->expectCategoryQuery)([10, 20], collect([$a, $b]));

    $transformer = new CategoryListTransformer;
    $result = $transformer->transform([10, 20]);

    expect($result)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);
    expect($result->pluck('id')->all())->toBe([10, 20]);
    expect($result->pluck('products_count')->all())->toBe([3, 5]);
});

it('preserves stored ID order', function () {
    $a = (object) ['id' => 10, 'name' => 'A', 'products_count' => 3];
    $b = (object) ['id' => 20, 'name' => 'B', 'products_count' => 5];

    ($this->expectCategoryQuery)([20, 10], collect([$a, $b]));

    $transformer = new CategoryListTransformer;
    $result = $transformer->transform([20, 10]);

    expect($result->pluck('id')->all())->toBe([20, 10]);
    expect($result->pluck('products_count')->all())->toBe([5, 3]);
});

it('skips missing IDs', function () {
    $a = (object) ['id' => 10, 'name' => 'A', 'products_count' => 3];

    ($this->expectCategoryQuery)([10, 999], collect([$a]));

    $transformer = new CategoryListTransformer;
    $result = $transformer->transform([10, 999]);

    expect($result->pluck('id')->all())->toBe([10]);
});

it('seeds the model cache for a later singular lookup', function () {
    $a = (object) ['id' => 10, 'name' => 'A', 'products_count' => 3];

    ($this->expectCategoryQuery)([10], collect([$a]));
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    (new CategoryListTransformer)->transform([10]);

    expect(app(ResolvedModelCache::class)->rememberCategory(10, fn () => 'not reached'))
        ->toBe($a);
});

it('resolves a repeated id once across two lists', function () {
    $a = (object) ['id' => 10, 'name' => 'A', 'products_count' => 3];

    ($this->expectCategoryQuery)([10], collect([$a]));
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $first = (new CategoryListTransformer)->transform([10]);
    $second = (new CategoryListTransformer)->transform([10]);

    expect($second->first())->toBe($first->first());
});

it('queries only the ids it has not already resolved', function () {
    $a = (object) ['id' => 10, 'name' => 'A', 'products_count' => 3];
    $b = (object) ['id' => 20, 'name' => 'B', 'products_count' => 5];

    ($this->expectCategoryQuery)([10], collect([$a]));
    ($this->expectCategoryQuery)([20], collect([$b]));
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    (new CategoryListTransformer)->transform([10]);

    expect((new CategoryListTransformer)->transform([10, 20])->pluck('id')->all())->toBe([10, 20]);
});

it('preloads resolved models in design mode', function () {
    $a = (object) ['id' => 10, 'name' => 'A', 'products_count' => 3];

    ($this->expectCategoryQuery)([10], collect([$a]));

    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldReceive('preloadModel')
        ->once()
        ->with('categories', $a);

    $transformer = new CategoryListTransformer;
    $transformer->transform([10]);
});
