<?php

use BagistoPlus\Visual\Settings\Radius;

it('defaults to md', function () {
    $setting = Radius::make('box_radius', 'Box radius');

    expect($setting->toArray())
        ->toHaveKey('type', 'radius')
        ->toHaveKey('default', 'md');
});

it('lets the default be overridden', function () {
    $setting = Radius::make('box_radius', 'Box radius')->default('full');

    expect($setting->toArray()['default'])->toBe('full');
});

it('keeps only known keys in options', function () {
    $setting = Radius::make('box_radius', 'Box radius')->options(['none', 'huge', 'sm', 'full']);

    expect($setting->toArray()['options'])->toBe(['none', 'sm', 'full']);
});
