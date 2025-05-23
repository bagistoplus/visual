<?php

use BagistoPlus\Visual\Sections\Support\BlockData;
use BagistoPlus\Visual\Settings\Support\SettingsValues;

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

    $block = BlockData::make($blockId, $blockData, $blockSchema, 'section-1');

    expect($block)
        ->id->toBe($blockId)
        ->type->toBe('image')
        ->name->toBe('image')
        ->disabled->toBeFalse()
        ->sectionId->toBe('section-1')
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
        settings: new SettingsValues(['font' => 'Arial'], ['font' => 'font']),
        disabled: true,
        sectionId: 'section-1',
    );

    expect($block->jsonSerialize())->toBe([
        'id' => 'block-1',
        'type' => 'text',
        'name' => 'Font',
        'disabled' => true,
        'settings' => ['font' => 'Arial'],
    ]);
});
