<?php

use BagistoPlus\Visual\Sections\BladeSection;
use BagistoPlus\Visual\Sections\SectionInterface;

it('should implements SectionInterface', function () {
    $mock = Mockery::mock(BladeSection::class)->makePartial();
    expect($mock)->toBeInstanceOf(SectionInterface::class);
});

it('merges additional methods to ignoredMethods list', function () {
    $section = new TestBladeSection('id', []);

    $reflection = new ReflectionMethod($section, 'ignoredMethods');
    $reflection->setAccessible(true);

    $ignoredMethods = $reflection->invoke($section);

    expect($ignoredMethods)
        ->toContain('slug', 'getSchemaPath', 'getSchema');
});

class TestBladeSection extends BladeSection {}
