<?php

use BagistoPlus\Visual\Settings\Support\ColorSchemeValue;

it('outputs one css variable per scheme token', function () {
    $css = (new ColorSchemeValue('default', [
        'primary' => '#92400e',
        'on-primary' => '#ffffff',
    ]))->outputCssVars();

    expect($css)
        ->toContain('--color-primary: oklch(')
        ->toContain('--color-on-primary: oklch(')
        ->and(substr_count($css, '--color-'))->toBe(2);
});

it('leaves lighter and darker variants to the theme', function () {
    $css = (new ColorSchemeValue('default', ['primary' => '#92400e']))->outputCssVars();

    expect($css)->not->toMatch('/--color-primary-\d+:/');
});

it('marks elements with its scheme id', function () {
    expect((string) (new ColorSchemeValue('dark'))->attributes())->toBe('data-color-scheme="dark"');
});
