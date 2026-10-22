<?php

use BagistoPlus\Visual\Support\SectionOptions;

function sameOptions(array $a, array $b): bool
{
    return (new SectionOptions)->same($a, $b);
}

it('reads the same keys in another order as the same options', function () {
    expect(sameOptions(
        ['css' => '.a{}', 'html' => '<p>x</p>'],
        ['html' => '<p>x</p>', 'css' => '.a{}'],
    ))->toBeTrue();
});

it('reads the same keys in another order inside a list entry as the same options', function () {
    expect(sameOptions(
        ['services' => [['title' => 'A', 'service_icon' => 'icon-truck']]],
        ['services' => [['service_icon' => 'icon-truck', 'title' => 'A']]],
    ))->toBeTrue();
});

it('reads a reordered list as a change, because that is what was dragged', function () {
    expect(sameOptions(
        ['images' => [['link' => '/one'], ['link' => '/two']]],
        ['images' => [['link' => '/two'], ['link' => '/one']]],
    ))->toBeFalse();
});

it('reads a changed value as a change', function () {
    expect(sameOptions(['title' => 'Summer'], ['title' => 'Winter']))->toBeFalse()
        ->and(sameOptions(['title' => 'Summer'], ['title' => 'Summer', 'extra' => 1]))->toBeFalse();
});

it('tells a value apart from one that merely looks like it', function () {
    expect(sameOptions(['limit' => 10], ['limit' => '10']))->toBeFalse()
        ->and(sameOptions(['on' => true], ['on' => 1]))->toBeFalse();
});
