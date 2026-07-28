<?php

use BagistoPlus\Visual\Blocks\SimpleBlock;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Settings\Select;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Support\EditorTranslationReferenceCollector;
use Craftile\Laravel\BlockSchemaRegistry;

class EditorTranslationReferenceCollectorBlock extends SimpleBlock
{
    protected static string $type = 'editor-translation-reference-collector-block';

    public static function settings(): array
    {
        return [
            Text::make('title', 'Title'),
            Text::make('responsive_title', 'Responsive title')->responsive(),
            Text::make('raw_title', 'Raw title')->localized(false),
            Select::make('handle', 'Handle')->options(['featured' => 'Featured']),
        ];
    }
}

beforeEach(function () {
    $registry = app(BlockSchemaRegistry::class);
    $registry->clear();
    $registry->register(BlockSchema::fromClass(EditorTranslationReferenceCollectorBlock::class));
});

it('records direct localized translation reference properties', function () {
    $collector = app(EditorTranslationReferenceCollector::class);
    $collector->reset();

    $collector->collect([
        'id' => 'hero',
        'type' => 'editor-translation-reference-collector-block',
        'properties' => [
            'title' => 't:block.title',
        ],
    ]);

    expect($collector->forBlockIds(['hero']))->toBe([
        'blocks' => [
            'hero' => [
                'properties' => [
                    'title' => 't:block.title',
                ],
            ],
        ],
    ]);
});

it('records responsive localized translation reference values individually', function () {
    $collector = app(EditorTranslationReferenceCollector::class);
    $collector->reset();

    $collector->collect([
        'id' => 'hero',
        'type' => 'editor-translation-reference-collector-block',
        'properties' => [
            'responsive_title' => [
                '_default' => 'desktop',
                'desktop' => 't:block.desktop',
                'mobile' => 'Custom mobile',
            ],
        ],
    ]);

    expect($collector->forBlockIds(['hero']))->toBe([
        'blocks' => [
            'hero' => [
                'properties' => [
                    'responsive_title' => [
                        'desktop' => 't:block.desktop',
                    ],
                ],
            ],
        ],
    ]);
});

it('ignores non-localized values, non-reference values, and non-responsive arrays', function () {
    $collector = app(EditorTranslationReferenceCollector::class);
    $collector->reset();

    $collector->collect([
        'id' => 'hero',
        'type' => 'editor-translation-reference-collector-block',
        'properties' => [
            'title' => 'Plain title',
            'raw_title' => 't:block.raw_title',
            'handle' => 't:block.handle',
        ],
    ]);

    expect($collector->forBlockIds(['hero']))->toBe([]);
});

it('filters references to rendered block ids', function () {
    $collector = app(EditorTranslationReferenceCollector::class);
    $collector->reset();

    $collector->collect([
        'id' => 'hero',
        'type' => 'editor-translation-reference-collector-block',
        'properties' => ['title' => 't:block.title'],
    ]);

    $collector->collect([
        'id' => 'unused',
        'type' => 'editor-translation-reference-collector-block',
        'properties' => ['title' => 't:block.unused'],
    ]);

    expect($collector->forBlockIds(['hero']))->toBe([
        'blocks' => [
            'hero' => [
                'properties' => [
                    'title' => 't:block.title',
                ],
            ],
        ],
    ]);
});
