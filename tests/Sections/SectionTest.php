<?php

use BagistoPlus\Visual\Sections\BladeSection;
use BagistoPlus\Visual\Sections\Section;

it('can be instantiated with default values', function () {
    $section = new Section('test-slug', 'Test Name');

    expect($section)
        ->slug->toBe('test-slug')
        ->name->toBe('Test Name')
        ->wrapper->toBe('div')
        ->settings->toBeArray()->toBeEmpty()
        ->blocks->toBeArray()->toBeEmpty()
        ->maxBlocks->toBe(16)
        ->description->toBeEmpty()
        ->previewImageUrl->toBeEmpty()
        ->previewDescription->toBeEmpty()
        ->default->toBeArray()->toBeEmpty()
        ->isLivewire->toBeFalse();
});

it('creates a section from component', function () {
    $section = Section::createFromComponent(TestSection::class);

    expect($section)
        ->slug->toBe('test-section')
        ->name->toBe('Test Section')
        ->wrapper->toBe('div')
        ->settings->toBeArray()->toHaveCount(1)
        ->maxBlocks->toBe(5)
        ->description->toBe('Test section description')
        ->and($section->settings[0])
        ->toBeArray()
        ->toHaveKey('id', 'setting1')
        ->toHaveKey('type', 'text');
})->todo();

it('returns an array representation of the section', function () {
    $section = new Section(
        slug: 'test-section',
        name: 'Test Section',
        wrapper: 'div',
        settings: ['setting1' => 'value'],
        blocks: [],
        maxBlocks: 10,
        description: 'Test Description',
        previewImageUrl: 'image.jpg',
        previewDescription: 'Preview Description',
        default: ['default_key' => 'default_value'],
        enabledOn: ['*'],
        disabledOn: [],
        isLivewire: true
    );

    $array = $section->toArray();

    expect($array)
        ->toHaveKey('slug', 'test-section')
        ->toHaveKey('name', 'Test Section')
        ->toHaveKey('wrapper', 'div')
        ->toHaveKey('settings.setting1', 'value')
        ->toHaveKey('maxBlocks', 10)
        ->toHaveKey('description', 'Test Description')
        ->toHaveKey('previewImageUrl', 'image.jpg')
        ->toHaveKey('previewDescription', 'Preview Description')
        ->toHaveKey('default.default_key', 'default_value')
        ->toHaveKey('isLivewire', true)
        ->toHaveKey('enabledOn', ['*'])
        ->toHaveKey('disabledOn', []);
});

it('serializes to JSON', function () {
    $section = new Section('test-section', 'Test Section', 'div');

    $json = json_encode($section);

    expect($json)->toContain('"slug":"test-section"');
    expect($json)->toContain('"name":"Test Section"');
    expect($json)->toContain('"wrapper":"div"');
});

it('renders a Section to blade template', function () {
    $section = new Section('test-section', 'Test Section');

    $id = '123456';
    $rendered = $section->renderToBlade($id);

    expect($rendered)
        ->toContain("<div data-section-type=\"test-section\" data-section-id=\"$id\"")
        ->toContain("<x-visual-section-test-section visualId=\"$id\"");
});

class TestSection extends BladeSection
{
    public static function getSchema(): array
    {
        return [
            'name' => 'Test Section',
            'wrapper' => 'div',
            'settings' => [
                [
                    'id' => 'setting1',
                    'type' => 'text',
                ],
            ],
            'blocks' => [],
            'maxBlocks' => 5,
            'description' => 'Test section description',
        ];
    }

    public function render()
    {
        return 'Test Section';
    }
}
