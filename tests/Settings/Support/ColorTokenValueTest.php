<?php

use BagistoPlus\Visual\Settings\Support\ColorTokenValue;

it('exposes the token via token()', function () {
    $value = new ColorTokenValue('primary');

    expect($value->token())->toBe('primary')
        ->and($value->isToken())->toBeTrue()
        ->and($value->isEmpty())->toBeFalse();
});

it('casts to the token string', function () {
    $value = new ColorTokenValue('secondary');

    expect((string) $value)->toBe('secondary');
});

it('returns the css variable for the token', function () {
    $value = new ColorTokenValue('danger');

    expect($value->cssVar())->toBe('var(--color-danger)');
});

it('exposes the canonical token list', function () {
    expect(ColorTokenValue::TOKENS)->toBe([
        'primary',
        'secondary',
        'accent',
        'neutral',
        'success',
        'warning',
        'danger',
        'info',
    ]);
});

it('exposes the sentinel for the explicit empty pick', function () {
    expect(ColorTokenValue::EMPTY_VALUE)->toBe('__none__');
});

it('builds an empty value via the empty() factory', function () {
    $value = ColorTokenValue::empty();

    expect($value->token())->toBeNull()
        ->and($value->isEmpty())->toBeTrue()
        ->and($value->isToken())->toBeFalse()
        ->and($value->cssVar())->toBeNull()
        ->and((string) $value)->toBe('');
});
