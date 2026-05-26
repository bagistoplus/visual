<?php

use BagistoPlus\Visual\Settings\Base;
use BagistoPlus\Visual\Settings\ColorToken;

it('extends Base', function () {
    expect(get_parent_class(ColorToken::class))->toBe(Base::class);
});

it('has correct type', function () {
    expect((new ColorToken('button_color', 'Button color'))->type())
        ->toBe('color_token');
});

it('includes id, label and type in toArray', function () {
    $array = ColorToken::make('button_color', 'Button color')->toArray();

    expect($array)
        ->toMatchArray([
            'id' => 'button_color',
            'label' => 'Button color',
            'type' => 'color_token',
        ]);
});

it('does not add an implicit default', function () {
    $array = ColorToken::make('button_color', 'Button color')->toArray();

    expect($array)->not->toHaveKey('default');
});

it('keeps default when explicitly configured', function () {
    $array = ColorToken::make('button_color', 'Button color')
        ->default('primary')
        ->toArray();

    expect($array)->toHaveKey('default')
        ->and($array['default'])->toBe('primary');
});

it('does not add allowNone to toArray by default', function () {
    $array = ColorToken::make('button_color', 'Button color')->toArray();

    expect($array)->not->toHaveKey('allowNone');
});

it('includes allowNone with the default label when called without arguments', function () {
    $array = ColorToken::make('button_color', 'Button color')
        ->allowNone()
        ->toArray();

    expect($array)->toHaveKey('allowNone')
        ->and($array['allowNone'])->toBe('None');
});

it('includes allowNone with a custom label when one is provided', function () {
    $array = ColorToken::make('button_color', 'Button color')
        ->allowNone('Custom')
        ->toArray();

    expect($array)->toHaveKey('allowNone')
        ->and($array['allowNone'])->toBe('Custom');
});
