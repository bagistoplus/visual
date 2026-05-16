<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Settings\Support\CmsPageListTransformer;
use Illuminate\Support\Collection;
use Webkul\CMS\Repositories\PageRepository;

beforeEach(function () {
    $this->repository = Mockery::mock(PageRepository::class);
    app()->instance(PageRepository::class, $this->repository);
});

afterEach(function () {
    Mockery::close();
});

it('implements SettingTransformerInterface', function () {
    expect(CmsPageListTransformer::class)
        ->toImplement(SettingTransformerInterface::class);
});

it('returns empty collection for null value', function () {
    $transformer = new CmsPageListTransformer;

    expect($transformer->transform(null))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('returns empty collection for non-array value', function () {
    $transformer = new CmsPageListTransformer;

    expect($transformer->transform('nope'))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('returns empty collection for empty array', function () {
    $transformer = new CmsPageListTransformer;

    expect($transformer->transform([]))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('resolves IDs through the page repository', function () {
    $a = (object) ['id' => 5, 'page_title' => 'A'];
    $b = (object) ['id' => 6, 'page_title' => 'B'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->with('id', [5, 6])
        ->andReturn(collect([$a, $b]));

    $transformer = new CmsPageListTransformer;
    $result = $transformer->transform([5, 6]);

    expect($result)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);
    expect($result->pluck('id')->all())->toBe([5, 6]);
});

it('preserves stored ID order', function () {
    $a = (object) ['id' => 5, 'page_title' => 'A'];
    $b = (object) ['id' => 6, 'page_title' => 'B'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->with('id', [6, 5])
        ->andReturn(collect([$a, $b]));

    $transformer = new CmsPageListTransformer;
    $result = $transformer->transform([6, 5]);

    expect($result->pluck('id')->all())->toBe([6, 5]);
});

it('skips missing IDs', function () {
    $a = (object) ['id' => 5, 'page_title' => 'A'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->andReturn(collect([$a]));

    $transformer = new CmsPageListTransformer;
    $result = $transformer->transform([5, 999]);

    expect($result->pluck('id')->all())->toBe([5]);
});

it('preloads resolved models under cms_pages key in design mode', function () {
    $a = (object) ['id' => 5, 'page_title' => 'A'];

    $this->repository
        ->shouldReceive('findWhereIn')
        ->once()
        ->andReturn(collect([$a]));

    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldReceive('preloadModel')
        ->once()
        ->with('cms_pages', $a);

    $transformer = new CmsPageListTransformer;
    $transformer->transform([5]);
});
