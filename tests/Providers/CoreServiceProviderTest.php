<?php

use BagistoPlus\Visual\Middlewares\DisableResponseCacheInDesignMode;
use Illuminate\Contracts\Http\Kernel;

it('registers design mode cache disabling middleware on the application http kernel', function () {
    $kernel = app(Kernel::class);
    $middleware = (new ReflectionProperty($kernel, 'middleware'))->getValue($kernel);

    expect($middleware)->toContain(DisableResponseCacheInDesignMode::class);
});
