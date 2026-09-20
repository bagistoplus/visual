<?php

use BagistoPlus\Visual\TemplateRegistrar;
use BagistoPlus\Visual\ThemeEditor;
use Illuminate\Support\Facades\Route;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\CMS\Repositories\PageRepository;
use Webkul\Product\Repositories\ProductRepository;

beforeEach(function () {
    foreach ([
        'shop.checkout.cart.index',
        'shop.checkout.onepage.index',
        'shop.search.index',
        'shop.home.contact_us',
    ] as $route) {
        Route::get('/fake/'.$route, fn () => '')->name($route);
    }
    Route::getRoutes()->refreshNameLookups();

    $categoryModel = Mockery::mock();
    $categoryModel->shouldReceive('has')->with('products')->andReturnSelf();
    $categoryModel->shouldReceive('first')->andReturn((object) ['id' => 1]);

    $categoryRepository = Mockery::mock(CategoryRepository::class);
    $categoryRepository->shouldReceive('getModel')->andReturn($categoryModel);
    app()->instance(CategoryRepository::class, $categoryRepository);

    $productRepository = Mockery::mock(ProductRepository::class);
    $productRepository->shouldReceive('first')->andReturn((object) ['id' => 1, 'url_key' => 'test-product']);
    app()->instance(ProductRepository::class, $productRepository);

    $pageRepository = Mockery::mock(PageRepository::class);
    $pageRepository->shouldReceive('first')->andReturn((object) ['id' => 1, 'url_key' => 'test-page']);
    app()->instance(PageRepository::class, $pageRepository);
});

afterEach(function () {
    Mockery::close();
});

it('marks product, category, and page templates as supporting variants only when template assignments are enabled', function (bool $enabled) {
    config()->set('bagisto_visual.template_assignments', $enabled);

    app(TemplateRegistrar::class)->registerTemplates();

    $captured = collect(app(ThemeEditor::class)->getTemplates())->keyBy('template');

    expect($captured['category']->supportsVariants)->toBe($enabled)
        ->and($captured['product']->supportsVariants)->toBe($enabled)
        ->and($captured['page']->supportsVariants)->toBe($enabled);
})->with([
    'enabled' => [true],
    'disabled' => [false],
]);
