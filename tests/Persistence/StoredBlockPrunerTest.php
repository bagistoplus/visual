<?php

use BagistoPlus\Visual\Blocks\SimpleBlock;
use BagistoPlus\Visual\Persistence\StoredBlockPruner;
use BagistoPlus\Visual\Settings\Number;
use BagistoPlus\Visual\Settings\Text;
use Craftile\Core\Data\BlockSchema;
use Craftile\Laravel\BlockSchemaRegistry;

class StoredBlockPrunerTestBlock extends SimpleBlock
{
    protected static string $type = 'pruner-block';

    public static function settings(): array
    {
        return [
            Text::make('align', 'Align')->localized(false)->default('center'),
            Text::make('title', 'Title')->default('Hello'),
            Number::make('gap', 'Gap')->default(4),
            Text::make('note', 'Note')->localized(false),
        ];
    }
}

function prunerBlock(array $overrides = []): array
{
    return array_merge([
        'id' => 'hero',
        'type' => 'pruner-block',
        'name' => 'Hero',
        'properties' => ['align' => 'center', 'title' => 'Hello', 'gap' => 4],
        'parentId' => 'section',
        'children' => ['child'],
        'disabled' => false,
        'static' => false,
        'repeated' => false,
        'ghost' => false,
        'keepInTree' => false,
        'semanticId' => null,
        'index' => 0,
        'iteration' => 1,
    ], $overrides);
}

function pruneBlocks(array $block, array $parentBlock = []): array
{
    $data = ['blocks' => ['hero' => $block]];
    $parent = $parentBlock === [] ? [] : ['blocks' => ['hero' => $parentBlock]];

    return app(StoredBlockPruner::class)->prune($data, $parent)['blocks']['hero'];
}

beforeEach(function () {
    $registry = new BlockSchemaRegistry;
    $registry->register(BlockSchema::fromClass(StoredBlockPrunerTestBlock::class));
    app()->instance(BlockSchemaRegistry::class, $registry);
});

it('drops properties equal to their schema default', function () {
    $pruned = pruneBlocks(prunerBlock());

    expect($pruned['properties'])->toBe(['title' => 'Hello']);
});

it('keeps properties that differ from the default', function () {
    $pruned = pruneBlocks(prunerBlock(['properties' => ['align' => 'left', 'gap' => 4]]));

    expect($pruned['properties'])->toBe(['align' => 'left']);
});

it('treats a missing schema default as null', function () {
    $dropped = pruneBlocks(prunerBlock(['properties' => ['note' => null]]));
    $kept = pruneBlocks(prunerBlock(['properties' => ['note' => 'Keep me']]));

    expect($dropped)->not->toHaveKey('properties')
        ->and($kept['properties'])->toBe(['note' => 'Keep me']);
});

it('never drops a localized property', function () {
    $pruned = pruneBlocks(prunerBlock(['properties' => ['title' => 'Hello']]));

    expect($pruned['properties'])->toBe(['title' => 'Hello']);
});

it('keeps a default when the parent sets a different value', function () {
    $pruned = pruneBlocks(
        prunerBlock(['properties' => ['align' => 'center']]),
        ['properties' => ['align' => 'left']]
    );

    expect($pruned['properties'])->toBe(['align' => 'center']);
});

it('removes the properties key once nothing is left', function () {
    $pruned = pruneBlocks(prunerBlock(['properties' => ['align' => 'center', 'gap' => 4]]));

    expect($pruned)->not->toHaveKey('properties');
});

it('drops false flags, null semantic ids, and derived positions', function () {
    $pruned = pruneBlocks(prunerBlock());

    expect($pruned)->not->toHaveKeys(['disabled', 'static', 'repeated', 'ghost', 'keepInTree', 'semanticId', 'index', 'iteration'])
        ->and($pruned)->toHaveKeys(['id', 'type', 'name', 'parentId', 'children']);
});

it('keeps a false flag that overrides a parent set to true', function () {
    $pruned = pruneBlocks(prunerBlock(), ['static' => true]);

    expect($pruned)->toHaveKey('static')
        ->and($pruned['static'])->toBeFalse()
        ->and($pruned)->not->toHaveKey('disabled');
});

it('keeps true flags and non null semantic ids', function () {
    $pruned = pruneBlocks(prunerBlock(['static' => true, 'semanticId' => 'main-hero']));

    expect($pruned['static'])->toBeTrue()
        ->and($pruned['semanticId'])->toBe('main-hero');
});

it('removes a child block entry that only repeated inherited defaults', function () {
    $data = ['blocks' => [
        'hero' => ['properties' => ['align' => 'center'], 'disabled' => false, 'index' => 2, 'iteration' => 1],
        'banner' => ['properties' => ['align' => 'left']],
    ]];
    $parent = ['blocks' => [
        'hero' => prunerBlock(['properties' => ['title' => 'Hello']]),
        'banner' => prunerBlock(['id' => 'banner']),
    ]];

    $pruned = app(StoredBlockPruner::class)->prune($data, $parent);

    expect($pruned['blocks'])->toBe(['banner' => ['properties' => ['align' => 'left']]]);
});

it('removes the blocks key once every entry is gone', function () {
    $data = ['blocks' => ['hero' => ['disabled' => false, 'index' => 0]], 'regions' => []];

    $pruned = app(StoredBlockPruner::class)->prune($data, ['blocks' => ['hero' => prunerBlock()]]);

    expect($pruned)->toBe(['regions' => []]);
});

it('leaves properties of unknown block types alone but still drops metadata', function () {
    $pruned = pruneBlocks(prunerBlock(['type' => 'unknown-block']));

    expect($pruned['properties'])->toBe(['align' => 'center', 'title' => 'Hello', 'gap' => 4])
        ->and($pruned)->not->toHaveKeys(['disabled', 'index', 'iteration']);
});
