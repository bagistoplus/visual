<?php

use BagistoPlus\Visual\Settings\Support\TypographyValue;
use Illuminate\Support\HtmlString;

it('constructs with all properties', function () {
    $data = [
        'fontFamily' => 'Inter',
        'fontSize' => '2xl',
        'lineHeight' => 'tight',
        'fontStyle' => 'italic',
        'letterSpacing' => 'wide',
        'textTransform' => 'uppercase',
    ];

    $value = new TypographyValue($data, 'heading');

    expect($value->fontFamily)
        ->toBeInstanceOf(\BagistoPlus\Visual\Settings\Support\FontValue::class)
        ->and($value->fontFamily->name)->toBe('Inter')
        ->and($value->fontSize)->toBe('2xl')
        ->and($value->lineHeight)->toBe('tight')
        ->and($value->fontStyle)->toBe('italic')
        ->and($value->letterSpacing)->toBe('wide')
        ->and($value->textTransform)->toBe('uppercase');
});

it('uses default values for missing properties', function () {
    $value = new TypographyValue([], 'test');

    expect($value->fontFamily)->toBeNull()
        ->and($value->fontStyle)->toBe('normal')
        ->and($value->fontSize)->toBe('text-base')
        ->and($value->lineHeight)->toBe('leading-normal')
        ->and($value->letterSpacing)->toBe('tracking-normal')
        ->and($value->textTransform)->toBe('none');
});

it('converts to string with ID', function () {
    $value = new TypographyValue([], 'heading');

    expect((string) $value)->toBe('heading');
});

it('generates HTML attributes', function () {
    $value = new TypographyValue([], 'body');

    $attributes = $value->attributes();

    expect($attributes)
        ->toBeInstanceOf(HtmlString::class)
        ->and($attributes->toHtml())
        ->toBe('data-typography="body"');
});

it('converts to array', function () {
    $data = [
        'fontFamily' => 'Roboto',
        'fontSize' => 'lg',
        'lineHeight' => 'relaxed',
        'fontStyle' => 'normal',
        'letterSpacing' => 'normal',
        'textTransform' => 'capitalize',
    ];

    $value = new TypographyValue($data, 'test');
    $array = $value->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveKeys(['fontFamily', 'fontStyle', 'fontSize', 'lineHeight', 'letterSpacing', 'textTransform'])
        ->and($array['fontFamily'])->toBe('Roboto')
        ->and($array['fontSize'])->toBe('lg')
        ->and($array['lineHeight'])->toBe('relaxed')
        ->and($array['fontStyle'])->toBe('normal')
        ->and($array['letterSpacing'])->toBe('normal')
        ->and($array['textTransform'])->toBe('capitalize');
});

it('generates CSS with all properties', function () {
    $data = [
        'fontFamily' => 'Inter',
        'fontSize' => 'xl',
        'lineHeight' => 'tight',
        'fontStyle' => 'italic',
        'letterSpacing' => 'wide',
        'textTransform' => 'uppercase',
    ];

    $value = new TypographyValue($data, 'heading');
    $css = $value->toCss();

    expect($css)
        ->toBeInstanceOf(HtmlString::class)
        ->and($css->toHtml())
        ->toContain('[data-typography="heading"]')
        ->toContain("--typography-font-family: 'Inter', sans-serif;")
        ->toContain('--typography-font-style: italic;')
        ->toContain('--typography-font-size: 1.25rem;')
        ->toContain('--typography-line-height: 1.25;')
        ->toContain('--typography-letter-spacing: 0.025em;')
        ->toContain('--typography-text-transform: uppercase;');
});

it('generates CSS without font family', function () {
    $data = [
        'fontFamily' => null,
        'fontSize' => 'base',
        'lineHeight' => 'normal',
        'fontStyle' => 'normal',
        'letterSpacing' => 'normal',
        'textTransform' => 'none',
    ];

    $value = new TypographyValue($data, 'test');
    $css = $value->toCss();

    expect($css->toHtml())
        ->not->toContain('--typography-font-family:');
});

it('converts font sizes to CSS values', function () {
    $sizes = [
        'xs' => '0.75rem',
        'sm' => '0.875rem',
        'base' => '1rem',
        'lg' => '1.125rem',
        'xl' => '1.25rem',
        '2xl' => '1.5rem',
        '3xl' => '1.875rem',
        '4xl' => '2.25rem',
    ];

    foreach ($sizes as $size => $expected) {
        $value = new TypographyValue(['fontSize' => $size], 'test');
        $css = $value->toCss()->toHtml();

        expect($css)->toContain("--typography-font-size: {$expected};");
    }
});

it('converts line heights to CSS values', function () {
    $lineHeights = [
        'none' => '1',
        'tight' => '1.25',
        'snug' => '1.375',
        'normal' => '1.5',
        'relaxed' => '1.625',
        'loose' => '2',
    ];

    foreach ($lineHeights as $lineHeight => $expected) {
        $value = new TypographyValue(['lineHeight' => $lineHeight], 'test');
        $css = $value->toCss()->toHtml();

        expect($css)->toContain("--typography-line-height: {$expected};");
    }
});

it('converts letter spacing to CSS values', function () {
    $spacings = [
        'tighter' => '-0.05em',
        'tight' => '-0.025em',
        'normal' => '0em',
        'wide' => '0.025em',
        'wider' => '0.05em',
        'widest' => '0.1em',
    ];

    foreach ($spacings as $spacing => $expected) {
        $value = new TypographyValue(['letterSpacing' => $spacing], 'test');
        $css = $value->toCss()->toHtml();

        expect($css)->toContain("--typography-letter-spacing: {$expected};");
    }
});

it('handles responsive font sizes', function () {
    $data = [
        'fontSize' => [
            '_default' => 'base',
            'mobile' => 'sm',
            'tablet' => 'md',
            'desktop' => 'lg',
        ],
        'lineHeight' => 'normal',
    ];

    $value = new TypographyValue($data, 'responsive');
    $css = $value->toCss()->toHtml();

    expect($css)
        ->toContain('--typography-font-size: 1rem;') // default
        ->toContain('@media (max-width: 639px)')
        ->toContain('--typography-font-size: 0.875rem;') // mobile
        ->toContain('@media (min-width: 1024px)')
        ->toContain('--typography-font-size: 1.125rem;'); // desktop
});

it('handles responsive line heights', function () {
    $data = [
        'fontSize' => 'base',
        'lineHeight' => [
            '_default' => 'normal',
            'mobile' => 'tight',
            'desktop' => 'relaxed',
        ],
    ];

    $value = new TypographyValue($data, 'responsive');
    $css = $value->toCss()->toHtml();

    expect($css)
        ->toContain('--typography-line-height: 1.5;') // default
        ->toContain('@media (max-width: 639px)')
        ->toContain('--typography-line-height: 1.25;') // mobile
        ->toContain('@media (min-width: 1024px)')
        ->toContain('--typography-line-height: 1.625;'); // desktop
});

it('uses first value as default when _default is missing', function () {
    $data = [
        'fontSize' => [
            'mobile' => 'sm',
            'desktop' => 'lg',
        ],
        'lineHeight' => 'normal',
    ];

    $value = new TypographyValue($data, 'test');
    $css = $value->toCss()->toHtml();

    expect($css)
        ->toContain('--typography-font-size: 0.875rem;'); // First value (mobile)
});

it('generates CSS with custom class selector added to default', function () {
    $data = [
        'fontFamily' => 'Inter',
        'fontSize' => 'xl',
        'lineHeight' => 'tight',
        'fontStyle' => 'normal',
        'letterSpacing' => 'normal',
        'textTransform' => 'none',
    ];

    $value = new TypographyValue($data, 'heading');
    $css = $value->toCss('.custom-heading');

    expect($css)
        ->toBeInstanceOf(HtmlString::class)
        ->and($css->toHtml())
        ->toContain('[data-typography="heading"], .custom-heading {')
        ->toContain('[data-typography="heading"]')
        ->toContain('.custom-heading');
});

it('generates CSS with custom element selector added to default', function () {
    $data = [
        'fontSize' => 'lg',
        'lineHeight' => 'relaxed',
    ];

    $value = new TypographyValue($data, 'test');
    $css = $value->toCss('h1')->toHtml();

    expect($css)
        ->toContain('[data-typography="test"], h1 {')
        ->toContain('[data-typography="test"]')
        ->toContain('h1');
});

it('generates CSS with complex selector added to default', function () {
    $data = ['fontSize' => 'base'];

    $value = new TypographyValue($data, 'test');
    $css = $value->toCss('.prose h1')->toHtml();

    expect($css)
        ->toContain('[data-typography="test"], .prose h1 {');
});

it('uses combined selector in responsive media queries', function () {
    $data = [
        'fontSize' => [
            '_default' => 'base',
            'mobile' => 'sm',
            'desktop' => 'lg',
        ],
        'lineHeight' => 'normal',
    ];

    $value = new TypographyValue($data, 'responsive');
    $css = $value->toCss('.custom')->toHtml();

    expect($css)
        ->toContain('[data-typography="responsive"], .custom {')
        ->toContain('@media (max-width: 639px)')
        ->toContain('  [data-typography="responsive"], .custom {')
        ->toContain('@media (min-width: 1024px)')
        ->toContain('[data-typography="responsive"]')
        ->toContain('.custom');
});

it('uses default selector when null is passed', function () {
    $value = new TypographyValue(['fontSize' => 'base'], 'test');
    $css = $value->toCss(null)->toHtml();

    expect($css)->toContain('[data-typography="test"] {');
});

it('preserves backward compatibility with no parameters', function () {
    $value = new TypographyValue(['fontSize' => 'base'], 'heading');

    $defaultCss = $value->toCss()->toHtml();
    $nullCss = $value->toCss(null)->toHtml();

    expect($defaultCss)
        ->toBe($nullCss)
        ->toContain('[data-typography="heading"]');
});

it('transforms fontFamily to FontValue instance', function () {
    $data = ['fontFamily' => 'Inter'];

    $value = new TypographyValue($data, 'test');

    expect($value->fontFamily)
        ->toBeInstanceOf(\BagistoPlus\Visual\Settings\Support\FontValue::class)
        ->and($value->fontFamily->name)
        ->toBe('Inter')
        ->and($value->fontFamily->slug)
        ->toBe('inter');
});

it('handles null fontFamily', function () {
    $value = new TypographyValue([], 'test');

    expect($value->fontFamily)->toBeNull();
});

it('casts FontValue to string in CSS generation', function () {
    $data = ['fontFamily' => 'Roboto', 'fontSize' => 'base'];

    $value = new TypographyValue($data, 'test');
    $css = $value->toCss()->toHtml();

    expect($css)
        ->toContain("--typography-font-family: 'Roboto', sans-serif;");
});

it('generates font loading HTML via toHtml()', function () {
    $data = ['fontFamily' => 'Inter'];

    $value = new TypographyValue($data, 'test');
    $html = $value->toHtml()->toHtml();

    expect($html)
        ->toContain('fonts.bunny.net')
        ->toContain('family=inter');
});

it('returns empty string for toHtml() when no fontFamily', function () {
    $value = new TypographyValue([], 'test');
    $html = $value->toHtml()->toHtml();

    expect($html)->toBe('');
});

it('converts fontFamily to name in toArray()', function () {
    $data = ['fontFamily' => 'Inter', 'fontSize' => 'base'];

    $value = new TypographyValue($data, 'test');
    $array = $value->toArray();

    expect($array)
        ->toHaveKey('fontFamily')
        ->and($array['fontFamily'])
        ->toBe('Inter');
});

it('converts IDs to kebab-case in attributes and CSS', function () {
    $data = ['fontSize' => 'base'];
    $value = new TypographyValue($data, 'Heading 1');

    expect($value->attributes()->toHtml())
        ->toBe('data-typography="heading1"');

    expect($value->toCss()->toHtml())
        ->toContain('[data-typography="heading1"]');
});
