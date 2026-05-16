<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Settings\Support\CategoryListTransformer;
use Illuminate\Support\Collection;
use Webkul\Category\Repositories\CategoryRepository;

beforeEach(function () {
    $this->repository = Mockery::mock(CategoryRepository::class);
    app()->instance(CategoryRepository::class, $this->repository);
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
    $a = (object) ['id' => 10, 'name' => 'A'];
    $b = (object) ['id' => 20, 'name' => 'B'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->with('id', [10, 20])
        ->andReturn(collect([$a, $b]));

    $transformer = new CategoryListTransformer;
    $result = $transformer->transform([10, 20]);

    expect($result)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);
    expect($result->pluck('id')->all())->toBe([10, 20]);
});

it('preserves stored ID order', function () {
    $a = (object) ['id' => 10, 'name' => 'A'];
    $b = (object) ['id' => 20, 'name' => 'B'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->with('id', [20, 10])
        ->andReturn(collect([$a, $b]));

    $transformer = new CategoryListTransformer;
    $result = $transformer->transform([20, 10]);

    expect($result->pluck('id')->all())->toBe([20, 10]);
});

it('skips missing IDs', function () {
    $a = (object) ['id' => 10, 'name' => 'A'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->andReturn(collect([$a]));

    $transformer = new CategoryListTransformer;
    $result = $transformer->transform([10, 999]);

    expect($result->pluck('id')->all())->toBe([10]);
});

it('preloads resolved models in design mode', function () {
    $a = (object) ['id' => 10, 'name' => 'A'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->andReturn(collect([$a]));

    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldReceive('preloadModel')
        ->once()
        ->with('categories', $a);

    $transformer = new CategoryListTransformer;
    $transformer->transform([10]);
});
