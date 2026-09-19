<?php

use BagistoPlus\Visual\Support\BagistoConfig;
use Webkul\Core\Core;
use Webkul\Core\Facades\Core as CoreFacade;

use function BagistoPlus\Visual\bagisto_config;

function bindCountingCore(mixed $answer = 'yes'): Core
{
    $core = Mockery::mock(Core::class);
    $core->shouldReceive('getConfigData')->andReturn($answer);

    CoreFacade::swap($core);
    app()->forgetInstance(BagistoConfig::class);

    return $core;
}

afterEach(function () {
    Mockery::close();
});

it('asks the store once however many times a key is read', function () {
    $core = bindCountingCore();

    $reads = collect(range(1, 5))
        ->map(fn () => bagisto_config('catalog.products.settings.compare_option'))
        ->all();

    expect($reads)->toBe(array_fill(0, 5, 'yes'));
    $core->shouldHaveReceived('getConfigData')->once();
});

it('asks once for each distinct key', function () {
    $core = bindCountingCore();

    bagisto_config('customer.settings.wishlist.wishlist_option');
    bagisto_config('catalog.products.settings.compare_option');
    bagisto_config('customer.settings.wishlist.wishlist_option');

    $core->shouldHaveReceived('getConfigData')->twice();
});

// A key with no row and no default answers null, and asking again would cost a
// query on every read, which is the case the memo exists for.
it('remembers that a key answered nothing', function () {
    $core = bindCountingCore(null);

    expect(bagisto_config('general.gdpr.cookie.enabled'))->toBeNull();
    expect(bagisto_config('general.gdpr.cookie.enabled'))->toBeNull();

    $core->shouldHaveReceived('getConfigData')->once();
});

it('keeps a key read for another channel or locale apart', function () {
    $core = bindCountingCore();

    bagisto_config('catalog.products.storefront.products_per_page');
    bagisto_config('catalog.products.storefront.products_per_page', 'uk');
    bagisto_config('catalog.products.storefront.products_per_page', 'uk', 'fr');
    bagisto_config('catalog.products.storefront.products_per_page', 'uk', 'fr');

    $core->shouldHaveReceived('getConfigData')->times(3);
});

// A package test suite need not register Bagisto's CoreServiceProvider, so the
// global core() helper may not exist at all where this runs.
it('reaches the store without the core helper', function () {
    $core = bindCountingCore('reached');

    expect((new BagistoConfig)->get('catalog.products.search.engine'))->toBe('reached');
});
