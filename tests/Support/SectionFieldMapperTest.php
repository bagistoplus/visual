<?php

use BagistoPlus\Visual\Settings\Code;
use BagistoPlus\Visual\Settings\Filters;
use BagistoPlus\Visual\Settings\Image;
use BagistoPlus\Visual\Settings\Number;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Textarea;
use BagistoPlus\Visual\Support\SectionFieldMapper;

function mapFields(array $fields): array
{
    return (new SectionFieldMapper)->properties($fields);
}

it('draws each field kind with the setting declared for it', function () {
    $properties = mapFields([
        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
        ['key' => 'body', 'type' => 'textarea', 'label' => 'Body'],
        ['key' => 'limit', 'type' => 'number', 'label' => 'Limit'],
        ['key' => 'banner', 'type' => 'image', 'label' => 'Banner'],
    ]);

    expect($properties)->toHaveCount(4)
        ->and($properties[0])->toBeInstanceOf(Text::class)
        ->and($properties[1])->toBeInstanceOf(Textarea::class)
        ->and($properties[2])->toBeInstanceOf(Number::class)
        ->and($properties[3])->toBeInstanceOf(Image::class)
        ->and($properties[0]->id)->toBe('title')
        ->and($properties[0]->label)->toBe('Title');
});

it('keeps the language a code field is written in', function () {
    $properties = mapFields([
        ['key' => 'css', 'type' => 'code', 'language' => 'css', 'label' => 'CSS'],
        ['key' => 'raw', 'type' => 'code', 'label' => 'Raw'],
    ]);

    expect($properties[0])->toBeInstanceOf(Code::class)
        ->and($properties[0]->toArray()['language'])->toBe('css')
        ->and($properties[1]->toArray())->not->toHaveKey('language');
});

it('keeps the keys a filter may be set on', function () {
    $keys = [
        ['value' => 'sort', 'label' => 'Sort', 'options' => [['value' => 'asc', 'label' => 'Asc']]],
        ['value' => 'limit', 'label' => 'Limit', 'options' => []],
    ];

    $properties = mapFields([
        ['key' => 'filters', 'type' => 'filters', 'label' => 'Filters', 'add_label' => 'Add', 'keys' => $keys],
    ]);

    expect($properties[0])->toBeInstanceOf(Filters::class)
        ->and($properties[0]->toArray()['keys'])->toBe($keys)
        ->and($properties[0]->toArray()['addLabel'])->toBe('Add');
});

it('marks every setting localized, because bagisto stores options per locale', function () {
    $properties = mapFields([
        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
        ['key' => 'limit', 'type' => 'number', 'label' => 'Limit'],
        ['key' => 'banner', 'type' => 'image', 'label' => 'Banner'],
    ]);

    foreach ($properties as $property) {
        expect($property->toArray()['localized'])->toBeTrue();
    }
});

it('leaves a kind it has no setting for uneditable rather than drawing the wrong one', function () {
    expect(mapFields([
        ['key' => 'mystery', 'type' => 'colour_wheel', 'label' => 'Mystery'],
        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
    ]))->toHaveCount(1);
});

it('ignores a field with no key or no kind', function () {
    expect(mapFields([
        ['type' => 'text', 'label' => 'Nameless'],
        ['key' => 'typeless', 'label' => 'Typeless'],
    ]))->toBeEmpty();
});

it('falls back to the key when a field carries no label', function () {
    expect(mapFields([['key' => 'title', 'type' => 'text']])[0]->label)->toBe('title');
});

it('leaves repeaters out of the settings and hands them over separately', function () {
    $fields = [
        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
        [
            'key' => 'images',
            'type' => 'repeater',
            'label' => 'Slider',
            'fields' => [['key' => 'image', 'type' => 'image', 'label' => 'Image']],
        ],
    ];

    $mapper = new SectionFieldMapper;

    expect($mapper->properties($fields))->toHaveCount(1)
        ->and($mapper->repeaters($fields))->toHaveKey('images')
        ->and($mapper->repeaters($fields)['images']['fields'])->toHaveCount(1);
});

it('stores an image as its path and anything else as it stands', function () {
    $mapper = new SectionFieldMapper;

    expect($mapper->storageValue('image', ['path' => 'a.webp', 'alt' => 'x']))->toBe('a.webp')
        ->and($mapper->storageValue('image', 'a.webp'))->toBe('a.webp')
        ->and($mapper->storageValue('image', null))->toBeNull()
        ->and($mapper->storageValue('image', ['alt' => 'no path']))->toBeNull()
        ->and($mapper->storageValue('text', ['not' => 'touched']))->toBe(['not' => 'touched']);
});

it('reports the kind each field is declared as', function () {
    expect((new SectionFieldMapper)->kinds([
        ['key' => 'title', 'type' => 'text'],
        ['key' => 'banner', 'type' => 'image'],
        ['type' => 'text'],
    ]))->toBe(['title' => 'text', 'banner' => 'image']);
});
