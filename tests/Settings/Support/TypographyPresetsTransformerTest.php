<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Settings\Support\TypographyPresetCollection;
use BagistoPlus\Visual\Settings\Support\TypographyPresetsTransformer;
use BagistoPlus\Visual\Settings\Support\TypographyValue;

it('implements SettingTransformerInterface', function () {
    expect(TypographyPresetsTransformer::class)
        ->toImplement(SettingTransformerInterface::class);
});

it('returns empty collection for null value', function () {
    $transformer = new TypographyPresetsTransformer;

    expect($transformer->transform(null))
        ->toBeInstanceOf(TypographyPresetCollection::class)
        ->toBeEmpty();
});

it('returns empty collection for non-array value', function () {
    $transformer = new TypographyPresetsTransformer;

    expect($transformer->transform('invalid'))
        ->toBeInstanceOf(TypographyPresetCollection::class)
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
        ->toBeInstanceOf(TypographyPresetCollection::class)
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

it('generates font links for configured typography font variants', function () {
    $transformer = new TypographyPresetsTransformer;

    $presets = [
        'heading' => [
            'fontFamily' => 'Inter',
            'fontWeight' => '700',
            'fontStyle' => 'normal',
        ],
        'heading-italic' => [
            'fontFamily' => 'Inter',
            'fontWeight' => '700',
            'fontStyle' => 'italic',
        ],
        'body' => [
            'fontFamily' => 'Inter',
            'fontWeight' => '400',
            'fontStyle' => 'normal',
        ],
        'accent' => [
            'fontFamily' => 'Roboto',
            'fontWeight' => '500',
            'fontStyle' => 'normal',
        ],
        'without-font' => [
            'fontWeight' => '300',
            'fontStyle' => 'normal',
        ],
    ];

    $html = $transformer->transform($presets)->fontLinks()->toHtml();

    expect($html)
        ->toContain('<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>')
        ->toContain('https://fonts.bunny.net/css?family=inter:700,700i,400,400i')
        ->toContain('https://fonts.bunny.net/css?family=roboto:500')
        ->toContain('rel="preload" as="style"')
        ->toContain('rel="stylesheet"');

    expect(substr_count($html, 'rel="preconnect"'))->toBe(1);
});

it('returns empty font links when no typography preset uses a font family', function () {
    $transformer = new TypographyPresetsTransformer;

    expect($transformer->transform([
        'heading' => ['fontWeight' => '700'],
    ])->fontLinks()->toHtml())->toBe('');
});
