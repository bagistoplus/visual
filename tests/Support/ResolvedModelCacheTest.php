<?php

use BagistoPlus\Visual\Support\ResolvedModelCache;

it('resolves a key once', function () {
    $cache = new ResolvedModelCache;
    $calls = 0;

    $first = $cache->remember('categories:1', function () use (&$calls) {
        $calls++;

        return (object) ['id' => 1];
    });

    $second = $cache->remember('categories:1', function () use (&$calls) {
        $calls++;

        return (object) ['id' => 1];
    });

    expect($calls)->toBe(1)
        ->and($second)->toBe($first);
});

it('remembers null results', function () {
    $cache = new ResolvedModelCache;
    $calls = 0;

    $cache->remember('categories:404', function () use (&$calls) {
        $calls++;

        return null;
    });

    $result = $cache->remember('categories:404', function () use (&$calls) {
        $calls++;

        return null;
    });

    expect($calls)->toBe(1)
        ->and($result)->toBeNull();
});

it('keys categories, products and cms pages separately', function () {
    $cache = new ResolvedModelCache;

    $category = $cache->rememberCategory(1, fn () => 'category');
    $product = $cache->rememberProduct(1, fn () => 'product');
    $page = $cache->rememberCmsPage(1, fn () => 'page');

    expect($category)->toBe('category')
        ->and($product)->toBe('product')
        ->and($page)->toBe('page')
        ->and($cache->rememberCategory(1, fn () => 'again'))->toBe('category');
});

it('is resolved as a scoped instance from the container', function () {
    expect(app(ResolvedModelCache::class))->toBe(app(ResolvedModelCache::class));
});

it('reads back what it remembered, and null for an unknown key', function () {
    $cache = new ResolvedModelCache;
    $product = (object) ['id' => 1];

    $cache->rememberProduct(1, fn () => $product);

    expect($cache->getProduct(1))->toBe($product)
        ->and($cache->getProduct(2))->toBeNull()
        ->and($cache->getCategory(1))->toBeNull()
        ->and($cache->getCmsPage(1))->toBeNull();
});
