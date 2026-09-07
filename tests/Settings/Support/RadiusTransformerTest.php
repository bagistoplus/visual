<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Settings\Support\RadiusTransformer;
use BagistoPlus\Visual\Settings\Support\RadiusValue;

it('implements SettingTransformerInterface', function () {
    expect(RadiusTransformer::class)->toImplement(SettingTransformerInterface::class);
});

it('transforms a known key into a radius value', function () {
    $value = (new RadiusTransformer)->transform('xl', ['type' => 'radius', 'default' => 'md']);

    expect($value)->toBeInstanceOf(RadiusValue::class)
        ->and($value->key)->toBe('xl')
        ->and((string) $value)->toBe('0.75rem');
});

it('falls back to the schema default for unknown or empty values', function () {
    $transformer = new RadiusTransformer;
    $schema = ['type' => 'radius', 'default' => 'none'];

    expect($transformer->transform('huge', $schema)->key)->toBe('none')
        ->and($transformer->transform(null, $schema)->key)->toBe('none')
        ->and($transformer->transform('', $schema)->key)->toBe('none');
});

it('falls back to md when the schema default is missing or unknown', function () {
    $transformer = new RadiusTransformer;

    expect($transformer->transform(null, ['type' => 'radius'])->key)->toBe('md')
        ->and($transformer->transform('huge', ['type' => 'radius', 'default' => 'huge'])->key)->toBe('md');
});
