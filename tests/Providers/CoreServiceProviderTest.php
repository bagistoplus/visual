<?php

use BagistoPlus\Visual\Middlewares\DisableResponseCacheInDesignMode;
use BagistoPlus\Visual\Middlewares\RegisterVisualSchemas;
use BagistoPlus\Visual\Providers\CoreServiceProvider;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Contracts\Http\Kernel;

it('does not register design mode cache disabling middleware on the application http kernel', function () {
    $kernel = app(Kernel::class);
    $middleware = (new ReflectionProperty($kernel, 'middleware'))->getValue($kernel);

    expect($middleware)->not->toContain(DisableResponseCacheInDesignMode::class);
});

it('registers visual storefront middlewares on the shop middleware group', function () {
    $middlewareGroups = app('router')->getMiddlewareGroups();
    $shopMiddleware = $middlewareGroups['shop'] ?? [];

    expect($shopMiddleware)
        ->toContain(DisableResponseCacheInDesignMode::class)
        ->toContain(RegisterVisualSchemas::class)
        ->and(array_search(DisableResponseCacheInDesignMode::class, $shopMiddleware, true))
        ->toBeLessThan(array_search(RegisterVisualSchemas::class, $shopMiddleware, true));
});

it('registers discovered schemas after the app boots in console', function () {
    Craftile::spy();

    $provider = new CoreServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'bootConsoleSchemaRegistration');
    $method->setAccessible(true);
    $method->invoke($provider);

    Craftile::shouldHaveReceived('registerDiscoveredSchemas')
        ->with(Mockery::on(fn ($filter) => is_callable($filter) && $filter([], 'block') === true))
        ->once();
});
