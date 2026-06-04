<?php

use BagistoPlus\Visual\Actions\GetProducts;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Queue;
use Webkul\Marketing\Jobs\UpdateCreateSearchTerm;
use Webkul\Product\Repositories\ProductRepository;

beforeEach(function () {
    $this->repository = Mockery::mock(ProductRepository::class);
    app()->instance(ProductRepository::class, $this->repository);
    app()->setLocale('en');
    app()->instance('request', Request::create('/products', 'GET'));
});

afterEach(function () {
    Mockery::close();
});

it('returns product paginator from the product repository', function () {
    $paginator = new LengthAwarePaginator([(object) ['id' => 1]], 1, 12);

    $this->repository
        ->shouldReceive('setSearchEngine')
        ->once()
        ->with('database')
        ->andReturnSelf();

    $this->repository
        ->shouldReceive('getSuggestions')
        ->never();

    $this->repository
        ->shouldReceive('getAll')
        ->once()
        ->with(Mockery::on(fn ($params) => $params === [
            'category_id' => 3,
            'limit' => 12,
            'sort' => 'price-desc',
            'price' => '0,75',
            'page' => 2,
            'query' => '',
            'channel_id' => 1,
            'status' => 1,
            'visible_individually' => 1,
        ]))
        ->andReturn($paginator);

    $products = app(GetProducts::class)->execute([
        'category_id' => 3,
        'limit' => 12,
        'sort' => 'price-desc',
        'price' => '0,75',
        'page' => 2,
    ]);

    expect($products)->toBe($paginator);
});

it('dispatches search term tracking for plain search requests', function () {
    Queue::fake();

    $paginator = new LengthAwarePaginator([(object) ['id' => 1]], 7, 12);

    $this->repository
        ->shouldReceive('setSearchEngine')
        ->once()
        ->with('database')
        ->andReturnSelf();

    $this->repository
        ->shouldReceive('getSuggestions')
        ->once()
        ->with('shirt')
        ->andReturn('shirts');

    $this->repository
        ->shouldReceive('getAll')
        ->once()
        ->with(Mockery::on(fn ($params) => $params['query'] === 'shirts'
            && $params['channel_id'] === 1
            && $params['status'] === 1
            && $params['visible_individually'] === 1))
        ->andReturn($paginator);

    app(GetProducts::class)->execute(['query' => 'shirt']);

    Queue::assertPushed(UpdateCreateSearchTerm::class, function ($job) {
        $property = new ReflectionProperty($job, 'data');
        $property->setAccessible(true);

        return $property->getValue($job) === [
            'term' => 'shirts',
            'results' => 7,
            'channel_id' => 1,
            'locale' => 'en',
        ];
    });
});

it('does not dispatch search term tracking when additional filters are present', function () {
    Queue::fake();

    $paginator = new LengthAwarePaginator([(object) ['id' => 1]], 1, 12);

    $this->repository
        ->shouldReceive('setSearchEngine')
        ->once()
        ->with('database')
        ->andReturnSelf();

    $this->repository
        ->shouldReceive('getSuggestions')
        ->once()
        ->with('shirt')
        ->andReturnNull();

    $this->repository
        ->shouldReceive('getAll')
        ->once()
        ->andReturn($paginator);

    app(GetProducts::class)->execute([
        'query' => 'shirt',
        'category_id' => 3,
    ]);

    Queue::assertNotPushed(UpdateCreateSearchTerm::class);
});

it('uses the original query without suggestions when suggestions are disabled', function () {
    Queue::fake();

    $paginator = new LengthAwarePaginator([(object) ['id' => 1]], 1, 12);

    $this->repository
        ->shouldReceive('setSearchEngine')
        ->once()
        ->with('database')
        ->andReturnSelf();

    $this->repository
        ->shouldReceive('getSuggestions')
        ->never();

    $this->repository
        ->shouldReceive('getAll')
        ->once()
        ->with(Mockery::on(fn ($params) => $params['query'] === 'shirt'
            && $params['suggest'] === '0'
            && $params['channel_id'] === 1
            && $params['status'] === 1
            && $params['visible_individually'] === 1))
        ->andReturn($paginator);

    app(GetProducts::class)->execute([
        'query' => 'shirt',
        'suggest' => '0',
    ]);

    Queue::assertNotPushed(UpdateCreateSearchTerm::class);
});
