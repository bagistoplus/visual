<?php

use BagistoPlus\Visual\Data\SectionRecord;
use BagistoPlus\Visual\Support\SectionPageBuilder;

function buildPage(array $records): array
{
    return app(SectionPageBuilder::class)->build($records);
}

it('reads a section row as a block carrying its own field values', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = buildPage([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, ['title' => 'Summer']),
    ]);

    expect($page['blocks']['section-7']['type'])->toBe('image_carousel')
        ->and($page['blocks']['section-7']['name'])->toBe('Hero')
        ->and($page['blocks']['section-7']['properties'])->toBe(['title' => 'Summer'])
        ->and($page['blocks']['section-7']['disabled'])->toBeFalse();
});

it('reads each entry of a repeating list as a block of its own', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = buildPage([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, [
            'title' => 'Summer',
            'images' => [
                ['image' => 'a.webp', 'link' => '/one'],
                ['image' => 'b.webp', 'link' => '/two'],
            ],
        ]),
    ]);

    expect($page['blocks']['section-7']['children'])->toBe(['section-7.images.0', 'section-7.images.1'])
        ->and($page['blocks'])->not->toHaveKey('section-7.images')
        ->and($page['blocks']['section-7.images.1']['properties'])->toBe(['image' => 'b.webp', 'link' => '/two'])
        ->and($page['blocks']['section-7.images.1']['index'])->toBe(1)
        ->and($page['blocks']['section-7.images.1']['parentId'])->toBe('section-7');
});

it('reads a list declared inside an entry', function () {
    offerTypes(['footer_links' => footerType()]);

    $page = buildPage([
        new SectionRecord(3, 'footer_links', 'Footer', true, 1, [
            'columns' => [
                ['links' => [['url' => '/about'], ['url' => '/terms']]],
            ],
        ]),
    ]);

    expect($page['blocks']['section-3']['children'])->toBe(['section-3.columns.0'])
        ->and($page['blocks']['section-3.columns.0']['children'])
        ->toBe(['section-3.columns.0.links.0', 'section-3.columns.0.links.1'])
        ->and($page['blocks']['section-3.columns.0.links.1']['properties'])->toBe(['url' => '/terms']);
});

it('keeps the sections a type draws on every page in their own region', function () {
    offerTypes(['image_carousel' => carouselType(), 'footer_links' => footerType()]);

    $page = buildPage([
        new SectionRecord(3, 'footer_links', 'Footer', true, 2, []),
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, []),
    ]);

    expect($page['regions'])->toBe([
        ['name' => 'content', 'shared' => false, 'blocks' => ['section-7']],
        ['name' => 'layout', 'shared' => true, 'blocks' => ['section-3']],
    ]);
});

it('arranges sections the way the rows are ordered', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = buildPage([
        new SectionRecord(2, 'image_carousel', 'Second', true, 20, []),
        new SectionRecord(1, 'image_carousel', 'First', true, 10, []),
    ]);

    expect($page['regions'][0]['blocks'])->toBe(['section-1', 'section-2']);
});

it('marks a section that is switched off as disabled rather than dropping it', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = buildPage([new SectionRecord(7, 'image_carousel', 'Hero', false, 1, [])]);

    expect($page['blocks']['section-7']['disabled'])->toBeTrue()
        ->and($page['regions'][0]['blocks'])->toBe(['section-7']);
});

it('leaves out a row whose type the theme no longer offers', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = buildPage([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, []),
        new SectionRecord(9, 'retired_type', 'Old', true, 2, []),
    ]);

    expect(array_keys($page['blocks']))->toBe(['section-7'])
        ->and($page['regions'][0]['blocks'])->toBe(['section-7']);
});

it('numbers entries from zero however bagisto keyed them', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = buildPage([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, [
            'images' => [3 => ['image' => 'a.webp'], 8 => ['image' => 'b.webp']],
        ]),
    ]);

    expect($page['blocks']['section-7']['children'])->toBe(['section-7.images.0', 'section-7.images.1'])
        ->and($page['blocks']['section-7.images.0']['properties'])->toBe(['image' => 'a.webp']);
});
