<?php

use BagistoPlus\Visual\Settings\Typography;

it('extends Base', function () {
    expect(get_parent_class(Typography::class))
        ->toBe('BagistoPlus\\Visual\\Settings\\Base');
});

it('has correct type', function () {
    $reflection = new ReflectionClass(Typography::class);
    $property = $reflection->getProperty('type');
    $property->setAccessible(true);

    expect($property->getValue(new Typography('test', 'Test')))
        ->toBe('typography');
});
