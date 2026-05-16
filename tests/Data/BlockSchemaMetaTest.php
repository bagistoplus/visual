<?php

use BagistoPlus\Visual\Blocks\SimpleBlock;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Http\Controllers\Admin\ThemeEditorController;
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

    $controller = (new ReflectionClass(ThemeEditorController::class))->newInstanceWithoutConstructor();
    $loadBlocks = new ReflectionMethod($controller, 'loadBlocks');
    $loadBlocks->setAccessible(true);

    $block = $loadBlocks->invoke($controller)->first();

    expect($block['meta'])
        ->toMatchArray([
            'name' => 'Schema Name',
            'icon' => 'schema-icon',
            'enabledOn' => ['product'],
            'badge' => 'Featured',
        ]);
});
