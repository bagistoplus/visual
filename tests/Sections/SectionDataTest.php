<?php

use BagistoPlus\Visual\Sections\Section;
use BagistoPlus\Visual\Sections\Support\BlockData;
use BagistoPlus\Visual\Sections\Support\SectionData;
use BagistoPlus\Visual\Settings\Support\SettingsValues;

it('can create a SectionData instance', function () {
    $sectionId = 'section-1';
    $sectionData = [
        'type' => 'header',
        'disabled' => false,
        'settings' => ['title' => 'Welcome'],
        'blocks' => [
            'block-1' => ['type' => 'image', 'settings' => ['size' => 'large']],
        ],
    ];

    $section = new Section(
        slug: 'header',
        name: 'Header',
        settings: [
            ['type' => 'text', 'id' => 'title', 'default' => 'Default Title'],
            ['type' => 'text', 'id' => 'subtitle', 'default' => 'Default Subtitle'],
        ],
        blocks: [
            ['type' => 'image', 'settings' => [['type' => 'text', 'id' => 'size', 'default' => 'medium']]],
        ]
    );

    $data = SectionData::make($sectionId, $sectionData, $section);

    expect($data)
        ->id->toBe($sectionId)
        ->type->toBe('header')
        ->name->toBe('Header')
        ->disabled->toBeFalse()
        ->blocks->toHaveCount(1)
        ->blocks_order->toBe(['block-1'])
        ->and($data->settings->toArray())->toBe([
            'title' => 'Welcome',
            'subtitle' => 'Default Subtitle', // default value
        ]);
});

it('serializes SectionData to JSON', function () {
    $blocks = [
        'block-1' => new BlockData(
            id: 'block-1',
            type: 'image',
            name: 'Image',
            disabled: false,
            settings: new SettingsValues(['size' => 'large'], ['size' => 'text']),
            sectionId: 'section-1',
        ),
    ];

    $section = new SectionData(
        id: 'section-1',
        type: 'header',
        name: 'Header',
        settings: new SettingsValues(['title' => 'My Title'], ['title' => 'text']),
        disabled: false,
        allBlocks: $blocks,
        blocks_order: ['block-1']
    );

    expect($section->jsonSerialize())->toBe([
        'id' => 'section-1',
        'type' => 'header',
        'name' => 'Header',
        'disabled' => false,
        'settings' => ['title' => 'My Title'],
        'blocks' => [
            'block-1' => [
                'id' => 'block-1',
                'type' => 'image',
                'name' => 'Image',
                'disabled' => false,
                'settings' => ['size' => 'large'],
            ],
        ],
        'blocks_order' => ['block-1'],
    ]);
});
