<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

it('registers acl entries matching the admin menu keys', function () {
    $aclKeys = collect(config('acl'))->pluck('key');

    foreach (config('menu.admin') as $menuItem) {
        if (! str_starts_with($menuItem['key'], 'bagisto_visual')) {
            continue;
        }

        expect($aclKeys)->toContain($menuItem['key']);
    }
});

it('maps every visual admin route to an acl entry', function () {
    $aclRoutes = collect(config('acl'))
        ->flatMap(fn ($item) => Arr::wrap($item['route']))
        ->all();

    $visualAdminRoutes = collect(Route::getRoutes()->getRoutesByName())
        ->keys()
        ->filter(fn ($name) => str_starts_with($name, 'visual.admin.'))
        ->values()
        ->all();

    expect($visualAdminRoutes)->not->toBeEmpty();

    foreach ($visualAdminRoutes as $routeName) {
        expect($aclRoutes)->toContain($routeName);
    }
});
