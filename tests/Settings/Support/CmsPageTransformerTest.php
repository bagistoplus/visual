<?php

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Settings\Support\CmsPageTransformer;
use Webkul\CMS\Repositories\PageRepository;

beforeEach(function () {
    $this->repository = Mockery::mock(PageRepository::class);
    app()->instance(PageRepository::class, $this->repository);
});

afterEach(function () {
    Mockery::close();
});

it('returns null for null id without querying', function () {
    $this->repository->shouldNotReceive('find');

    $transformer = new CmsPageTransformer;

    expect($transformer(null))->toBeNull();
});

it('resolves the cms page by id', function () {
    $model = (object) ['id' => 10];

    $this->repository->shouldReceive('find')->once()->with(10)->andReturn($model);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $transformer = new CmsPageTransformer;

    expect($transformer(10))->toBe($model);
});

it('queries the same id once per request', function () {
    $model = (object) ['id' => 10];

    $this->repository->shouldReceive('find')->once()->with(10)->andReturn($model);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $transformer = new CmsPageTransformer;

    expect($transformer(10))->toBe($model)
        ->and($transformer(10))->toBe($model);
});

it('queries a missing id once per request', function () {
    $this->repository->shouldReceive('find')->once()->with(404)->andReturn(null);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(false);

    $transformer = new CmsPageTransformer;

    expect($transformer(404))->toBeNull()
        ->and($transformer(404))->toBeNull();
});

it('preloads the resolved model in design mode', function () {
    $model = (object) ['id' => 10];

    $this->repository->shouldReceive('find')->once()->with(10)->andReturn($model);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldReceive('preloadModel')->once()->with('cms_pages', $model);

    $transformer = new CmsPageTransformer;
    $transformer(10);
});

it('does not preload a missing model in design mode', function () {
    $this->repository->shouldReceive('find')->once()->with(404)->andReturn(null);
    ThemeEditor::shouldReceive('inDesignMode')->andReturn(true);
    ThemeEditor::shouldNotReceive('preloadModel');

    $transformer = new CmsPageTransformer;

    expect($transformer(404))->toBeNull();
});
