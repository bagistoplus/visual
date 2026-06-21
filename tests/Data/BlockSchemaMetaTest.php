<?php

use BagistoPlus\Visual\Blocks\SimpleBlock;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Settings\Header;
use BagistoPlus\Visual\Settings\Select;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Support\EditorBlockSchemaSerializer;
use Craftile\Core\Data\BlockPreset;
use Craftile\Laravel\BlockSchemaRegistry;

class BlockSchemaMetaBlock extends SimpleBlock
{
    protected static array $meta = [
        'badge' => 'New',
        'priority' => 10,
        'searchable' => true,
        'tags' => ['hero', 'campaign'],
        'source' => null,
    ];
}

class BlockSchemaMetaReservedKeysBlock extends SimpleBlock
{
    protected static string $name = 'Schema Name';

    protected static string $icon = 'schema-icon';

    protected static array $enabledOn = ['product'];

    protected static array $meta = [
        'name' => 'Custom Name',
        'icon' => 'custom-icon',
        'enabledOn' => ['page'],
        'badge' => 'Featured',
    ];
}

class TranslatedBlockSchemaPreset extends BlockPreset
{
    protected function build(): void
    {
        $this
            ->name('t:schema.preset_name')
            ->description('schema.preset_description')
            ->category('t:schema.preset_category')
            ->properties(['title' => 't:schema.raw_title'])
            ->blocks([
                [
                    'type' => 'child',
                    'name' => 't:schema.child_name',
                    'properties' => ['title' => 't:schema.raw_child_title'],
                    'children' => [
                        [
                            'type' => 'nested-child',
                            'name' => 't:schema.nested_child_name',
                            'properties' => ['title' => 't:schema.raw_nested_child_title'],
                        ],
                    ],
                ],
            ]);
    }
}

class TranslatedBlockSchemaBlock extends SimpleBlock
{
    protected static string $name = 't:schema.block_name';

    protected static string $description = 'schema.block_description';

    protected static string $category = 't:schema.block_category';

    public static function settings(): array
    {
        return [
            Header::make('t:schema.group'),
            Text::make('title', 't:schema.title')->info('schema.title_info'),
            Select::make('style', 'schema.style')->options([
                'featured' => 't:schema.option_featured',
                'plain' => 'Plain option',
            ]),
        ];
    }

    public static function presets(): array
    {
        return [
            TranslatedBlockSchemaPreset::make(),
            [
                'name' => 't:schema.array_preset_name',
                'properties' => ['title' => 't:schema.array_raw_title'],
            ],
        ];
    }
}

it('adds custom JSON-safe meta to block schemas', function () {
    $schema = BlockSchema::fromClass(BlockSchemaMetaBlock::class);

    expect($schema->meta)->toBe([
        'badge' => 'New',
        'priority' => 10,
        'searchable' => true,
        'tags' => ['hero', 'campaign'],
        'source' => null,
    ]);

    expect($schema->toArray()['meta'])->toBe($schema->meta);
});

it('keeps built-in admin meta keys reserved', function () {
    $registry = new BlockSchemaRegistry;
    $registry->register(BlockSchema::fromClass(BlockSchemaMetaReservedKeysBlock::class));
    app()->instance(BlockSchemaRegistry::class, $registry);

    $block = app(EditorBlockSchemaSerializer::class)->all()[0];

    expect($block['meta'])
        ->toMatchArray([
            'name' => 'Schema Name',
            'icon' => 'schema-icon',
            'enabledOn' => ['product'],
            'badge' => 'Featured',
        ]);
});

it('translates editor block schema ui text without translating starter data', function () {
    app('translator')->addLines([
        'schema.block_name' => 'Translated block',
        'schema.block_description' => 'Translated block description',
        'schema.block_category' => 'Sections',
        'schema.group' => 'Content',
        'schema.title' => 'Title',
        'schema.title_info' => 'Title info',
        'schema.style' => 'Style',
        'schema.option_featured' => 'Featured option',
        'schema.preset_name' => 'Hero preset',
        'schema.preset_description' => 'Preset description',
        'schema.preset_category' => 'Presets',
        'schema.array_preset_name' => 'Array preset',
        'schema.child_name' => 'Child block',
        'schema.nested_child_name' => 'Nested child block',
        'schema.raw_title' => 'Raw title',
        'schema.raw_child_title' => 'Raw child title',
        'schema.raw_nested_child_title' => 'Raw nested child title',
        'schema.array_raw_title' => 'Array raw title',
    ], 'en');

    $registry = new BlockSchemaRegistry;
    $registry->register(BlockSchema::fromClass(TranslatedBlockSchemaBlock::class));
    app()->instance(BlockSchemaRegistry::class, $registry);

    $block = app(EditorBlockSchemaSerializer::class)->all()[0];

    expect($block['meta'])
        ->toMatchArray([
            'name' => 'Translated block',
            'description' => 'Translated block description',
            'category' => 'Sections',
        ])
        ->and($block['properties'][0])
        ->toMatchArray([
            'label' => 'Title',
            'info' => 'Title info',
            'group' => 'Content',
        ])
        ->and($block['properties'][1]['label'])->toBe('Style')
        ->and($block['properties'][1]['options'][0]['label'])->toBe('Featured option')
        ->and($block['properties'][1]['options'][0]['value'])->toBe('featured')
        ->and($block['presets'][0])
        ->toMatchArray([
            'name' => 'Hero preset',
            'description' => 'Preset description',
            'category' => 'Presets',
            'properties' => ['title' => 't:schema.raw_title'],
            'children' => [
                [
                    'type' => 'child',
                    'name' => 'Child block',
                    'properties' => ['title' => 't:schema.raw_child_title'],
                    'children' => [
                        [
                            'type' => 'nested-child',
                            'name' => 'Nested child block',
                            'properties' => ['title' => 't:schema.raw_nested_child_title'],
                        ],
                    ],
                ],
            ],
        ])
        ->and($block['presets'][1])
        ->toMatchArray([
            'name' => 'Array preset',
            'properties' => ['title' => 't:schema.array_raw_title'],
        ]);
});
