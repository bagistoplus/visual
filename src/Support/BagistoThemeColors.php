<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Settings\Color;

/**
 * The colours a theme Bagisto draws the sections of is edited with.
 *
 * Such a theme has no settings schema of its own: it is a plain Bagisto theme,
 * with no service provider of Visual's to load one from. It declares its
 * colours in its `config/themes.php` entry instead, under `customize.colors`,
 * and a theme that declares none is given the two the stock theme is built on.
 *
 * A colour reaches the storefront as a stylesheet overriding the utilities the
 * theme was compiled with. Nothing else is possible: the stock theme's palette
 * is literal hex in `tailwind.config.js`, compiled into class names spread
 * across every blade, with no custom property anywhere to reassign. Coverage is
 * therefore whatever the declared classes reach, and no more.
 */
class BagistoThemeColors
{
    /**
     * What a theme is given when it declares nothing.
     *
     * These are the only two brand colours the stock theme's blades actually
     * use. The three others its tailwind config names appear in no markup, so
     * offering them would be offering controls that change nothing.
     */
    public const DEFAULTS = [
        'brand' => [
            'label' => 'Brand',
            'default' => '#060C3B',
            'classes' => ['navyBlue'],
            // The buttons are component classes with the colour written into
            // their own body, so overriding the utilities does not reach them.
            'rules' => [
                '.primary-button' => ['background-color', 'border-color'],
                '.secondary-button' => ['color', 'border-color'],
            ],
        ],
        'accent' => [
            'label' => 'Accent',
            'default' => '#F6F2EB',
            'classes' => ['lightOrange'],
            'rules' => [],
        ],
    ];

    /**
     * The utilities a colour class is compiled into, and what each one sets.
     */
    protected const UTILITIES = [
        'text' => 'color',
        'bg' => 'background-color',
        'border' => 'border-color',
        'ring' => '--tw-ring-color',
    ];

    /**
     * The variants the stock theme uses, which are written out because their
     * compiled selectors carry a pseudo-class and outweigh a plain one.
     */
    protected const VARIANTS = [
        'hover' => ':hover',
        'focus' => ':focus',
        'checked' => ':checked',
    ];

    /**
     * The colours a theme offers, as it declared them.
     *
     * @param  array<string, mixed>  $customize  The theme's `customize` entry.
     * @return array<string, array{label: string, default: string, classes: array<int, string>, rules: array<string, array<int, string>>}>
     */
    public function declarations(array $customize): array
    {
        $declared = $customize['colors'] ?? null;

        if (! is_array($declared) || $declared === []) {
            return self::DEFAULTS;
        }

        $colors = [];

        foreach ($declared as $id => $color) {
            if (! is_string($id) || ! is_array($color)) {
                continue;
            }

            $colors[$id] = [
                'label' => is_string($color['label'] ?? null) ? $color['label'] : $id,
                'default' => is_string($color['default'] ?? null) ? $color['default'] : '',
                'classes' => array_values(array_filter(
                    (array) ($color['classes'] ?? []),
                    fn ($class) => is_string($class) && $class !== ''
                )),
                'rules' => $this->normalizeRules($color['rules'] ?? []),
            ];
        }

        return $colors;
    }

    /**
     * The settings schema the editor draws those colours with, in the one shape
     * the rest of Visual reads a schema in.
     *
     * @param  array<string, mixed>  $customize
     * @return array<int, array<string, mixed>>
     */
    public function schema(array $customize): array
    {
        $colors = $this->declarations($customize);

        if ($colors === []) {
            return [];
        }

        $settings = [];

        foreach ($colors as $id => $color) {
            $settings[] = Color::make($id, $color['label'])->default($color['default'])->toArray();
        }

        return [[
            'name' => 'Colors',
            'settings' => $settings,
        ]];
    }

    /**
     * The stylesheet that puts the chosen colours over the compiled ones.
     *
     * @param  array<string, mixed>  $customize
     * @param  array<string, mixed>  $values  What the merchant chose, by colour id.
     */
    public function css(array $customize, array $values): string
    {
        $rules = [];

        foreach ($this->declarations($customize) as $id => $color) {
            $value = $values[$id] ?? $color['default'];

            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $value = trim($value);

            foreach ($color['classes'] as $class) {
                $rules = array_merge($rules, $this->rulesFor($class, $value));
            }

            foreach ($color['rules'] as $selector => $properties) {
                $rules[] = $selector.'{'.implode('', array_map(
                    fn (string $property) => $property.':'.$value.';',
                    $properties
                )).'}';
            }
        }

        return implode('', $rules);
    }

    /**
     * The selectors a colour drives directly, as the theme declared them.
     *
     * @param  mixed  $rules
     * @return array<string, array<int, string>>
     */
    protected function normalizeRules($rules): array
    {
        if (! is_array($rules)) {
            return [];
        }

        $normalized = [];

        foreach ($rules as $selector => $properties) {
            $properties = array_values(array_filter(
                (array) $properties,
                fn ($property) => is_string($property) && $property !== ''
            ));

            if (is_string($selector) && $selector !== '' && $properties !== []) {
                $normalized[$selector] = $properties;
            }
        }

        return $normalized;
    }

    /**
     * Every selector one colour class is compiled into, given the value it is
     * to hold.
     *
     * @return array<int, string>
     */
    protected function rulesFor(string $class, string $value): array
    {
        $rules = [];

        foreach (self::UTILITIES as $utility => $property) {
            $rules[] = '.'.$utility.'-'.$class.'{'.$property.':'.$value.'}';

            foreach (self::VARIANTS as $variant => $pseudo) {
                $rules[] = '.'.$variant.'\\:'.$utility.'-'.$class.$pseudo.'{'.$property.':'.$value.'}';
            }
        }

        return $rules;
    }
}
