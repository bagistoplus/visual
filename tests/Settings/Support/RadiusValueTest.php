<?php

use BagistoPlus\Visual\Settings\Support\RadiusValue;

it('exposes the key and the css length', function () {
    $value = new RadiusValue('lg');

    expect($value->key)->toBe('lg')
        ->and($value->value)->toBe('0.5rem')
        ->and((string) $value)->toBe('0.5rem');
});

it('follows the tailwind radius scale', function () {
    expect(RadiusValue::SCALE)->toBe([
        'none' => '0',
        'xs' => '0.125rem',
        'sm' => '0.25rem',
        'md' => '0.375rem',
        'lg' => '0.5rem',
        'xl' => '0.75rem',
        'full' => 'calc(infinity * 1px)',
    ]);
});

it('falls back to the md length for unknown keys', function () {
    $value = new RadiusValue('huge');

    expect($value->key)->toBe('huge')
        ->and((string) $value)->toBe('0.375rem');
});

it('reports whether a key is known', function () {
    expect(RadiusValue::has('xl'))->toBeTrue()
        ->and(RadiusValue::has('huge'))->toBeFalse()
        ->and(RadiusValue::has(null))->toBeFalse()
        ->and(RadiusValue::has(['md']))->toBeFalse();
});
