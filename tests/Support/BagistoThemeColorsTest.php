<?php

use BagistoPlus\Visual\Support\BagistoThemeColors;
use BagistoPlus\Visual\Theme\Theme;

function colors(): BagistoThemeColors
{
    return new BagistoThemeColors;
}

it('gives a theme that declares nothing the colours the stock theme is built on', function () {
    expect(colors()->declarations([]))->toBe(BagistoThemeColors::DEFAULTS)
        ->and(array_keys(BagistoThemeColors::DEFAULTS))->toBe(['brand', 'accent']);
});

it('reads the colours a theme declared', function () {
    $declared = colors()->declarations([
        'colors' => [
            'primary' => ['label' => 'Primary', 'default' => '#ff0000', 'classes' => ['brandRed']],
        ],
    ]);

    expect($declared)->toBe([
        'primary' => ['label' => 'Primary', 'default' => '#ff0000', 'classes' => ['brandRed'], 'rules' => []],
    ]);
});

it('names a colour after its id when it was given no label', function () {
    expect(colors()->declarations(['colors' => ['primary' => ['default' => '#ff0000']]]))
        ->toBe(['primary' => ['label' => 'primary', 'default' => '#ff0000', 'classes' => [], 'rules' => []]]);
});

it('recolours the buttons, whose colour is written into their own class', function () {
    $css = colors()->css([], ['brand' => '#123456']);

    expect($css)->toContain('.primary-button{background-color:#123456;border-color:#123456;}')
        ->and($css)->toContain('.secondary-button{color:#123456;border-color:#123456;}');
});

it('drives whatever selectors a theme named', function () {
    $css = colors()->css([
        'colors' => [
            'brand' => [
                'default' => '#123456',
                'rules' => ['.btn' => ['background-color']],
            ],
        ],
    ], []);

    expect($css)->toBe('.btn{background-color:#123456;}');
});

it('leaves out a rule that names no property', function () {
    $declared = colors()->declarations([
        'colors' => ['brand' => ['rules' => ['.btn' => [], '' => ['color']]]],
    ]);

    expect($declared['brand']['rules'])->toBe([]);
});

it('draws each colour as a colour setting', function () {
    $schema = colors()->schema([]);

    expect($schema)->toHaveCount(1)
        ->and($schema[0]['name'])->toBe('Colors')
        ->and(array_column($schema[0]['settings'], 'id'))->toBe(['brand', 'accent'])
        ->and($schema[0]['settings'][0]['type'])->toBe('color')
        ->and($schema[0]['settings'][0]['default'])->toBe('#060C3B');
});

it('overrides every utility a colour class was compiled into', function () {
    $css = colors()->css(
        ['colors' => ['brand' => ['label' => 'Brand', 'default' => '#000000', 'classes' => ['navyBlue']]]],
        ['brand' => '#123456'],
    );

    expect($css)->toContain('.text-navyBlue{color:#123456}')
        ->and($css)->toContain('.bg-navyBlue{background-color:#123456}')
        ->and($css)->toContain('.border-navyBlue{border-color:#123456}')
        ->and($css)->toContain('.ring-navyBlue{--tw-ring-color:#123456}');
});

it('overrides the variants too, whose compiled selectors outweigh a plain one', function () {
    $css = colors()->css([], ['brand' => '#123456']);

    expect($css)->toContain('.hover\:border-navyBlue:hover{border-color:#123456}')
        ->and($css)->toContain('.focus\:ring-navyBlue:focus{--tw-ring-color:#123456}')
        ->and($css)->toContain('.checked\:text-navyBlue:checked{color:#123456}');
});

it('falls back to what a colour was declared as when the merchant chose nothing', function () {
    expect(colors()->css([], []))->toContain('.text-navyBlue{color:#060C3B}');
});

it('writes nothing for a colour left empty', function () {
    $css = colors()->css(
        ['colors' => ['brand' => ['default' => '', 'classes' => ['navyBlue']]]],
        ['brand' => '  '],
    );

    expect($css)->toBe('');
});

it('writes nothing for a colour that names neither a class nor a selector', function () {
    expect(colors()->css(['colors' => ['brand' => ['default' => '#123456']]], []))->toBe('');
});

it('stands in as the settings schema of a theme that has none of its own', function () {
    $theme = Theme::make(['code' => 'default', 'name' => 'Default', 'base_path' => '/tmp']);

    expect(array_column($theme->settingsSchema[0]['settings'], 'id'))->toBe(['brand', 'accent']);
});

it('leaves a visual theme with no settings alone', function () {
    $theme = Theme::make([
        'code' => 'visual-demo',
        'name' => 'Visual Demo',
        'base_path' => '/tmp',
        'visual_theme' => true,
    ]);

    expect($theme->settingsSchema)->toBe([]);
});

it('leaves a theme that carries its own schema alone', function () {
    $theme = Theme::make([
        'code' => 'default',
        'name' => 'Default',
        'base_path' => '/tmp',
        'settings_schema' => [['name' => 'Layout', 'settings' => []]],
    ]);

    expect($theme->settingsSchema)->toBe([['name' => 'Layout', 'settings' => []]]);
});

it('reads a theme under the code its config entry is keyed by', function () {
    config()->set('themes.shop.default', ['name' => 'Default']);

    $theme = Theme::forCode('default');

    expect($theme->code)->toBe('default')
        ->and(array_column($theme->settingsSchema[0]['settings'], 'id'))->toBe(['brand', 'accent']);
});

it('answers nothing for a theme that is not registered', function () {
    expect(Theme::forCode('nope'))->toBeNull();
});
