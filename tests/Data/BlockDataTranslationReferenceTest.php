<?php

use BagistoPlus\Visual\Blocks\SimpleBlock;
use BagistoPlus\Visual\Data\BlockData;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Settings\Select;
use BagistoPlus\Visual\Settings\Text;
use Craftile\Laravel\BlockSchemaRegistry;

class BlockDataTranslationReferenceBlock extends SimpleBlock
{
    protected static string $type = 'translation-reference-block';

    public static function settings(): array
    {
        return [
            Text::make('title', 'Title')->default('t:block.default_title'),
            Text::make('responsive_title', 'Responsive title')->responsive(),
            Text::make('structured_title', 'Structured title'),
            Select::make('handle', 'Handle')->options(['featured' => 'Featured']),
            Text::make('raw_title', 'Raw title')->localized(false),
        ];
    }
}

beforeEach(function () {
    app('translator')->addLines([
        'block.default_title' => 'Default title',
        'block.title' => 'Translated title',
        'block.desktop' => 'Desktop title',
        'block.mobile' => 'Mobile title',
        'block.structured' => 'Structured title',
        'block.handle' => 'Translated handle',
        'block.unknown' => 'Translated unknown',
        'block.raw_title' => 'Translated raw title',
        'block.name' => 'Translated block name',
    ], 'en');

    $registry = app(BlockSchemaRegistry::class);
    $registry->clear();
    $registry->register(BlockSchema::fromClass(BlockDataTranslationReferenceBlock::class));
});

it('resolves translation references only for localized block properties', function () {
    $block = BlockData::make([
        'id' => 'hero',
        'type' => 'translation-reference-block',
        'properties' => [
            'title' => 't:block.title',
            'handle' => 't:block.handle',
            'unknown' => 't:block.unknown',
            'raw_title' => 't:block.raw_title',
        ],
    ]);

    expect($block->properties->raw())->toMatchArray([
        'title' => 'Translated title',
        'handle' => 't:block.handle',
        'unknown' => 't:block.unknown',
        'raw_title' => 't:block.raw_title',
    ]);
});

it('resolves localized schema defaults during block data creation', function () {
    $block = BlockData::make([
        'id' => 'hero',
        'type' => 'translation-reference-block',
    ]);

    expect($block->properties->raw()['title'])->toBe('Default title');
});

it('recursively resolves responsive localized property values', function () {
    $block = BlockData::make([
        'id' => 'hero',
        'type' => 'translation-reference-block',
        'properties' => [
            'responsive_title' => [
                '_default' => 'desktop',
                'desktop' => 't:block.desktop',
                'mobile' => 't:block.mobile',
            ],
            'structured_title' => [
                'label' => 't:block.structured',
            ],
            'handle' => [
                '_default' => 'desktop',
                'desktop' => 't:block.handle',
            ],
        ],
    ]);

    expect($block->properties->raw()['responsive_title'])->toBe([
        '_default' => 'desktop',
        'desktop' => 'Desktop title',
        'mobile' => 'Mobile title',
    ])->and($block->properties->raw()['structured_title'])->toBe([
        'label' => 't:block.structured',
    ])->and($block->properties->raw()['handle'])->toBe([
        '_default' => 'desktop',
        'desktop' => 't:block.handle',
    ]);
});

it('leaves translation references raw when block schema is missing', function () {
    app(BlockSchemaRegistry::class)->clear();

    $block = BlockData::make([
        'id' => 'hero',
        'type' => 'missing-schema-block',
        'properties' => [
            'title' => 't:block.title',
        ],
    ]);

    expect($block->properties->raw())->toBe([
        'title' => 't:block.title',
    ]);
});

it('resolves translation references in block names', function (?string $name, ?string $expected) {
    $block = BlockData::make([
        'id' => 'hero',
        'type' => 'translation-reference-block',
        'name' => $name,
    ]);

    expect($block->name)->toBe($expected);
})->with([
    'translated reference' => ['t:block.name', 'Translated block name'],
    'custom name' => ['Custom name', 'Custom name'],
    'empty reference' => ['t:', 't:'],
    'missing name' => [null, null],
]);
