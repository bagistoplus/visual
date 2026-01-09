<?php

use BagistoPlus\Visual\Settings\TypographyPresets;

it('extends Base', function () {
    expect(get_parent_class(TypographyPresets::class))
        ->toBe('BagistoPlus\\Visual\\Settings\\Base');
});

it('has correct type', function () {
    $reflection = new ReflectionClass(TypographyPresets::class);
    $property = $reflection->getProperty('type');
    $property->setAccessible(true);

    expect($property->getValue(new TypographyPresets('test', 'Test')))
        ->toBe('typography_presets');
});

it('sets presets in meta and default', function () {
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
            'fontFamily' => 'Inter',
            'fontSize' => 'base',
            'lineHeight' => 'normal',
            'fontStyle' => 'normal',
            'letterSpacing' => 'normal',
            'textTransform' => 'none',
        ],
    ];

    $setting = TypographyPresets::make('test_presets', 'Test Presets')
        ->presets($presets);

    $array = $setting->toArray();

    expect($array)
        ->toHaveKey('presets')
        ->and($array['presets'])
        ->toBe($presets)
        ->and($array)
        ->toHaveKey('default')
        ->and($array['default'])
        ->toBe($presets);
});

it('returns static instance from presets method', function () {
    $setting = TypographyPresets::make('test_presets', 'Test Presets');

    expect($setting->presets([]))
        ->toBeInstanceOf(TypographyPresets::class)
        ->toBe($setting);
});
