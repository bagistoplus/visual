<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Settings\Support\ColorTokenTransformer;
use BagistoPlus\Visual\Settings\Support\ColorTokenValue;

it('implements SettingTransformerInterface', function () {
    expect(ColorTokenTransformer::class)->toImplement(SettingTransformerInterface::class);
});

it('returns null for null', function () {
    expect((new ColorTokenTransformer)->transform(null))->toBeNull();
});

it('returns null for empty string', function () {
    expect((new ColorTokenTransformer)->transform(''))->toBeNull();
});

it('returns null for non-string values', function () {
    $transformer = new ColorTokenTransformer;

    expect($transformer->transform(0))->toBeNull()
        ->and($transformer->transform(1))->toBeNull()
        ->and($transformer->transform(1.5))->toBeNull()
        ->and($transformer->transform(true))->toBeNull()
        ->and($transformer->transform(false))->toBeNull()
        ->and($transformer->transform(['primary']))->toBeNull()
        ->and($transformer->transform(['token' => 'primary']))->toBeNull()
        ->and($transformer->transform(new stdClass))->toBeNull();
});

it('returns null for tokens outside the allowed list', function ($value) {
    expect((new ColorTokenTransformer)->transform($value))->toBeNull();
})->with([
    'background',
    'surface',
    'surface-alt',
    'on-primary',
    'unknown',
    'PRIMARY',
    ' primary',
]);

it('transforms each valid token into a ColorTokenValue', function ($token) {
    $value = (new ColorTokenTransformer)->transform($token);

    expect($value)
        ->toBeInstanceOf(ColorTokenValue::class)
        ->and($value->token())->toBe($token)
        ->and($value->isToken())->toBeTrue()
        ->and($value->isEmpty())->toBeFalse()
        ->and($value->cssVar())->toBe("var(--color-{$token})");
})->with(ColorTokenValue::TOKENS);

it('transforms the sentinel into an empty ColorTokenValue', function () {
    $value = (new ColorTokenTransformer)->transform(ColorTokenValue::EMPTY_VALUE);

    expect($value)
        ->toBeInstanceOf(ColorTokenValue::class)
        ->and($value->isEmpty())->toBeTrue()
        ->and($value->isToken())->toBeFalse()
        ->and($value->token())->toBeNull()
        ->and($value->cssVar())->toBeNull();
});
