<?php

use BagistoPlus\Visual\Support\SchemaTextTranslator;

beforeEach(function () {
    app('translator')->addLines([
        'schema.hero' => 'Hero',
        'schema.description' => 'Hero description',
        'schema.group' => 'Content',
        'schema.option' => 'Featured',
        'schema.child' => 'Child',
        'schema.nested_child' => 'Nested child',
    ], 'en');
});

it('translates schema text references and plain translation keys', function () {
    $translator = app(SchemaTextTranslator::class);

    expect($translator->translateText('t:schema.hero'))->toBe('Hero')
        ->and($translator->translateText('schema.description'))->toBe('Hero description')
        ->and($translator->translateText('Plain label'))->toBe('Plain label')
        ->and($translator->translateText(''))->toBe('')
        ->and($translator->translateText('t:'))->toBe('')
        ->and($translator->translateText(null))->toBeNull();
});

it('translates only known schema ui keys and option labels', function () {
    $translator = app(SchemaTextTranslator::class);

    $schema = $translator->translatePropertySchema([
        'id' => 'hero',
        'type' => 'select',
        'label' => 't:schema.hero',
        'info' => 'schema.description',
        'group' => 't:schema.group',
        'default' => 't:schema.default',
        'options' => [
            ['value' => 'featured', 'label' => 't:schema.option'],
            ['value' => 'raw', 'label' => 'Raw'],
        ],
    ]);

    expect($schema)
        ->toMatchArray([
            'label' => 'Hero',
            'info' => 'Hero description',
            'group' => 'Content',
            'default' => 't:schema.default',
            'options' => [
                ['value' => 'featured', 'label' => 'Featured'],
                ['value' => 'raw', 'label' => 'Raw'],
            ],
        ]);
});

it('translates theme settings schema ui text', function () {
    $translator = app(SchemaTextTranslator::class);

    $schema = $translator->translateThemeSettingsSchema([
        [
            'name' => 't:schema.group',
            'settings' => [
                [
                    'id' => 'style',
                    'type' => 'select',
                    'label' => 't:schema.hero',
                    'options' => [
                        ['value' => 'featured', 'label' => 't:schema.option'],
                    ],
                ],
            ],
        ],
    ]);

    expect($schema[0]['name'])->toBe('Content')
        ->and($schema[0]['settings'][0]['label'])->toBe('Hero')
        ->and($schema[0]['settings'][0]['options'][0]['label'])->toBe('Featured');
});

it('recursively translates preset child names only', function () {
    $translator = app(SchemaTextTranslator::class);

    $preset = $translator->translatePreset([
        'name' => 't:schema.hero',
        'children' => [
            [
                'type' => 'child',
                'id' => 'child-id',
                'name' => 't:schema.child',
                'properties' => ['title' => 't:schema.raw_title'],
                'static' => true,
                'children' => [
                    [
                        'type' => 'nested',
                        'name' => 't:schema.nested_child',
                        'properties' => ['title' => 't:schema.raw_nested_title'],
                    ],
                    [
                        'type' => 'empty-reference',
                        'name' => 't:',
                    ],
                ],
                'order' => ['nested'],
            ],
            'unexpected-child',
        ],
    ]);

    expect($preset['children'][0])
        ->toMatchArray([
            'type' => 'child',
            'id' => 'child-id',
            'name' => 'Child',
            'properties' => ['title' => 't:schema.raw_title'],
            'static' => true,
            'order' => ['nested'],
            'children' => [
                [
                    'type' => 'nested',
                    'name' => 'Nested child',
                    'properties' => ['title' => 't:schema.raw_nested_title'],
                ],
                [
                    'type' => 'empty-reference',
                    'name' => '',
                ],
            ],
        ])
        ->and($preset['children'][1])->toBe('unexpected-child');
});
