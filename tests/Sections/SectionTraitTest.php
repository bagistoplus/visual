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

class SectionTraitTest
{
    use SectionTrait;
}
