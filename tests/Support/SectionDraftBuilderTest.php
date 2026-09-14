<?php

use BagistoPlus\Visual\Data\SectionDraft;
use BagistoPlus\Visual\Data\SectionRecord;
use BagistoPlus\Visual\Support\SectionDraftBuilder;
use BagistoPlus\Visual\Support\SectionPageBuilder;
use Illuminate\Support\Collection;

function draftsFrom(array $page, array $existing = []): Collection
{
    return app(SectionDraftBuilder::class)->build($page['blocks'], $page['regions'], $existing);
}

it('reads a section block back as the options its row should hold', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = app(SectionPageBuilder::class)->build([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, [
            'title' => 'Summer',
            'images' => [['image' => 'a.webp', 'link' => '/one']],
        ]),
    ]);

    $draft = draftsFrom($page)->first();

    expect($draft)->toBeInstanceOf(SectionDraft::class)
        ->and($draft->rowId)->toBe(7)
        ->and($draft->status)->toBeTrue()
        ->and($draft->sortOrder)->toBe(1)
        ->and($draft->options)->toBe([
            'title' => 'Summer',
            'images' => [['image' => 'a.webp', 'link' => '/one']],
        ]);
});

it('survives a round trip unchanged', function () {
    offerTypes(['footer_links' => footerType()]);

    $options = [
        'columns' => [
            ['links' => [['url' => '/about'], ['url' => '/terms']]],
            ['links' => [['url' => '/help']]],
        ],
    ];

    $page = app(SectionPageBuilder::class)->build([
        new SectionRecord(3, 'footer_links', 'Footer', true, 1, $options),
    ]);

    expect(draftsFrom($page, [3 => $options])->first()->options)->toBe($options);
});

it('rebuilds a list from the order it holds its entries in, not from their ids', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = app(SectionPageBuilder::class)->build([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, [
            'images' => [['link' => '/one'], ['link' => '/two'], ['link' => '/three']],
        ]),
    ]);

    $page['blocks']['section-7.images']['children'] = [
        'section-7.images.2',
        'section-7.images.0',
    ];

    expect(draftsFrom($page)->first()->options['images'])
        ->toBe([['image' => null, 'link' => '/three'], ['image' => null, 'link' => '/one']]);
});

it('numbers content before the sections drawn on every page', function () {
    offerTypes(['image_carousel' => carouselType(), 'footer_links' => footerType()]);

    $page = app(SectionPageBuilder::class)->build([
        new SectionRecord(3, 'footer_links', 'Footer', true, 1, []),
        new SectionRecord(7, 'image_carousel', 'Hero', true, 2, []),
        new SectionRecord(8, 'image_carousel', 'Promo', true, 3, []),
    ]);

    expect(draftsFrom($page)->map(fn (SectionDraft $d) => [$d->rowId, $d->sortOrder])->all())
        ->toBe([[7, 1], [8, 2], [3, 3]]);
});

it('numbers sections by where the page holds them, not by where they were', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = app(SectionPageBuilder::class)->build([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, []),
        new SectionRecord(8, 'image_carousel', 'Promo', true, 2, []),
    ]);

    $page['regions'][0]['blocks'] = ['section-8', 'section-7'];

    expect(draftsFrom($page)->map(fn (SectionDraft $d) => [$d->rowId, $d->sortOrder])->all())
        ->toBe([[8, 1], [7, 2]]);
});

it('reads a disabled block back as a section switched off', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = app(SectionPageBuilder::class)->build([
        new SectionRecord(7, 'image_carousel', 'Hero', false, 1, []),
    ]);

    expect(draftsFrom($page)->first()->status)->toBeFalse();
});

it('leaves an option the type does not declare as it was', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = app(SectionPageBuilder::class)->build([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, ['title' => 'Summer']),
    ]);

    $options = draftsFrom($page, [7 => ['title' => 'Summer', 'legacy' => 'kept']])->first()->options;

    expect($options['legacy'])->toBe('kept')
        ->and($options['title'])->toBe('Summer');
});

it('empties a list the editor removed every entry from', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = app(SectionPageBuilder::class)->build([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, [
            'images' => [['link' => '/one']],
        ]),
    ]);

    $page['blocks']['section-7.images']['children'] = [];

    expect(draftsFrom($page, [7 => ['images' => [['link' => '/one']]]])->first()->options['images'])->toBe([]);
});

it('ignores a block that is not a section row', function () {
    offerTypes(['image_carousel' => carouselType()]);

    $page = app(SectionPageBuilder::class)->build([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, []),
    ]);

    $page['regions'][0]['blocks'] = ['section-7', 'section-7.images', 'stray'];

    expect(draftsFrom($page)->pluck('rowId')->all())->toBe([7]);
});
