<?php

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Sections\BladeSection;
use BagistoPlus\Visual\Sections\Section;
use BagistoPlus\Visual\Sections\SectionInterface;
use BagistoPlus\Visual\Sections\Support\SectionData;

it('should implements SectionInterface', function () {
    $mock = Mockery::mock(BladeSection::class)->makePartial();
    expect($mock)->toBeInstanceOf(SectionInterface::class);
});

it('merges additional methods to ignoredMethods list', function () {
    Visual::themeDataCollector()->setSectionData('id', SectionData::make('id', [], new Section('test', 'test')));
    $section = new TestBladeSection('id', []);

    $reflection = new ReflectionMethod($section, 'ignoredMethods');
    $reflection->setAccessible(true);

    $ignoredMethods = $reflection->invoke($section);

    expect($ignoredMethods)
        ->toContain('slug', 'name', 'getSchemaPath', 'getSchema', 'getViewData');
});

class TestBladeSection extends BladeSection {}
