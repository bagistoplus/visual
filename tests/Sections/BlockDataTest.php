<?php

use BagistoPlus\Visual\Sections\Concerns\BlockData;
use Illuminate\Support\Fluent;

it('can create a BlockData instance', function () {
    $blockId = 'block-1';
    $blockData = [
        'type' => 'image',
        'disabled' => false,
        'settings' => [
            'size' => 'large',
        ],
    ];

    $blockSchema = [
        'type' => 'image',
        'settings' => [
            ['type' => 'radio', 'id' => 'size', 'default' => 'medium'],
            ['type' => 'radio', 'id' => 'alignment', 'default' => 'center'],
        ],
    ];

    $block = BlockData::make($blockId, $blockData, $blockSchema);

    expect($block)
        ->id->toBe($blockId)
        ->type->toBe('image')
        ->name->toBe('image')
        ->disabled->toBeFalse()
        ->and($block->settings->toArray())->toBe([
            'size' => 'large',  // custom value
            'alignment' => 'center', // default value
        ]);
});

it('serializes BlockData to JSON', function () {
    $block = new BlockData(
        id: 'block-1',
        type: 'text',
        name: 'Font',
        settings: new Fluent(['font' => 'Arial']),
        disabled: true
    );

    expect($block->jsonSerialize())->toBe([
        'id' => 'block-1',
        'type' => 'text',
        'name' => 'Font',
        'disabled' => true,
        'settings' => ['font' => 'Arial'],
    ]);
});
