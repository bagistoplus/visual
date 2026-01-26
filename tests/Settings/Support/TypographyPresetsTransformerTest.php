<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Settings\Support\TypographyPresetsTransformer;
use BagistoPlus\Visual\Settings\Support\TypographyValue;

it('implements SettingTransformerInterface', function () {
    expect(TypographyPresetsTransformer::class)
        ->toImplement(SettingTransformerInterface::class);
});

it('returns empty collection for null value', function () {
    $transformer = new TypographyPresetsTransformer;

    expect($transformer->transform(null))
        ->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->toBeEmpty();
});

it('returns empty collection for non-array value', function () {
    $transformer = new TypographyPresetsTransformer;

    expect($transformer->transform('invalid'))
        ->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->toBeEmpty();
});

it('transforms array to collection of TypographyValue objects', function () {
    $transformer = new TypographyPresetsTransformer;

    $presets = [
        'heading' => [
            'fontFamily' => 'Inter',
            'fontSize' => '2xl',
            'lineHeight' => 'tight',
            'fontStyle' => 'normal',
            'letterSpacing' => 'normal',
            'textTransform' => 'none',
        ],
        'body' => [
            'fontFamily' => 'Roboto',
            'fontSize' => 'base',
            'lineHeight' => 'normal',
            'fontStyle' => 'normal',
            'letterSpacing' => 'normal',
            'textTransform' => 'none',
        ],
    ];

    $result = $transformer->transform($presets);

    expect($result)
        ->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->toHaveCount(2)
        ->each(fn ($item) => $item->toBeInstanceOf(TypographyValue::class));

    expect($result['heading'])
        ->toBeInstanceOf(TypographyValue::class)
        ->and((string) $result['heading'])
        ->toBe('heading')
        ->and($result['body'])
        ->toBeInstanceOf(TypographyValue::class)
        ->and((string) $result['body'])
        ->toBe('body');
});

it('preserves preset IDs when transforming', function () {
    $transformer = new TypographyPresetsTransformer;

    $presets = [
        'custom-preset' => [
            'fontFamily' => 'Arial',
            'fontSize' => 'lg',
            'lineHeight' => 'relaxed',
            'fontStyle' => 'italic',
            'letterSpacing' => 'wide',
            'textTransform' => 'uppercase',
        ],
    ];

    $result = $transformer->transform($presets);

    expect($result->keys()->toArray())
        ->toBe(['custom-preset'])
        ->and((string) $result['custom-preset'])
        ->toBe('custom-preset');
});
