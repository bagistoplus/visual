<?php

use BagistoPlus\Visual\Sections\Concerns\SectionTrait;

it('returns the slug from static property if set', function () {
    $reflection = new ReflectionClass(SectionTraitTest::class);
    $slugProp = $reflection->getProperty('slug');
    $slugProp->setAccessible(true);
    $slugProp->setValue('custom-slug');

    expect(SectionTraitTest::slug())->toBe('custom-slug');
});

it('it should generate the correct default slug', function () {
    $reflection = new ReflectionClass(SectionTraitTest::class);
    $slugProp = $reflection->getProperty('slug');
    $slugProp->setAccessible(true);
    $slugProp->setValue('');

    expect(SectionTraitTest::slug())->toBe('section-trait-test');
});

it('returns the schema path from the static property', function () {
    $reflection = new ReflectionClass(SectionTraitTest::class);
    $schemaProp = $reflection->getProperty('schema');
    $schemaProp->setAccessible(true);
    $schemaProp->setValue('path/to/schema.json');

    expect(SectionTraitTest::getSchemaPath())->toBe('path/to/schema.json');
});

it('throws an exception if schema path is invalid', function () {
    $reflection = new ReflectionClass(SectionTraitTest::class);
    $schemaProp = $reflection->getProperty('schema');
    $schemaProp->setAccessible(true);

    $schemaProp->setValue('');
    expect(fn() => SectionTraitTest::getSchema())
        ->toThrow(Exception::class, 'Invalid schema file path');

    $schemaProp->setValue('/path/that/doesnt/exists');
    expect(fn() => SectionTraitTest::getSchema())
        ->toThrow(Exception::class, 'Invalid schema file path');
});

it('returns the schema array when a valid schema path is provided', function () {
    $schemaPath = tempnam(sys_get_temp_dir(), 'schema.json');
    $reflection = new ReflectionClass(SectionTraitTest::class);
    $schemaProp = $reflection->getProperty('schema');
    $schemaProp->setAccessible(true);
    $schemaProp->setValue($schemaPath);

    $schema = ['key' => 'value'];
    file_put_contents($schemaPath, json_encode($schema));

    expect(SectionTraitTest::getSchema())->toBe($schema);
});

class SectionTraitTest
{
    use SectionTrait;
}
