<?php

use BagistoPlus\Visual\Data\SectionDraft;
use BagistoPlus\Visual\Data\SectionRecord;
use BagistoPlus\Visual\Persistence\PersistSectionUpdates;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Support\SectionDraftBuilder;
use BagistoPlus\Visual\Support\SectionPageBuilder;
use Craftile\Laravel\Data\UpdateRequest;
use Illuminate\Support\Collection;

function recordingStore(array $records): SectionStore
{
    return new class($records) extends SectionStore
    {
        public array $saved = [];

        public array $savedWith = [];

        public function __construct(protected array $records) {}

        public function records(int $channelId, string $themeCode, string $locale): Collection
        {
            return collect($this->records);
        }

        public array $previous = [];

        public function saveDrafts(
            Collection $drafts,
            int $channelId,
            string $themeCode,
            string $locale,
            array $previousOptions = []
        ): void {
            $this->saved = $drafts->all();
            $this->savedWith = [$channelId, $themeCode, $locale];
            $this->previous = $previousOptions;
        }
    };
}

function persistUpdates(SectionStore $store, array $updates): void
{
    $persister = new PersistSectionUpdates(
        $store,
        app(SectionPageBuilder::class),
        app(SectionDraftBuilder::class),
    );

    $persister->handle(UpdateRequest::make($updates), 1, 'default', 'en');
}

function heroRow(array $options = []): SectionRecord
{
    return new SectionRecord(7, 'image_carousel', 'Hero', true, 1, $options);
}

function contentRegion(array $blocks): array
{
    return [['name' => 'content', 'shared' => false, 'blocks' => $blocks]];
}

beforeEach(function () {
    offerTypes(['image_carousel' => carouselType()]);
});

it('lays a changed block over the page the rows describe', function () {
    $store = recordingStore([heroRow(['title' => 'Summer', 'images' => [['link' => '/one']]])]);

    persistUpdates($store, [
        'blocks' => [
            'section-7' => [
                'id' => 'section-7',
                'type' => 'image_carousel',
                'name' => 'Hero',
                'properties' => ['title' => 'Winter'],
                'children' => ['section-7.images.0'],
            ],
        ],
        'regions' => contentRegion(['section-7']),
        'changes' => ['added' => [], 'updated' => ['section-7'], 'removed' => []],
    ]);

    expect($store->saved)->toHaveCount(1)
        ->and($store->saved[0]->options['title'])->toBe('Winter')
        ->and($store->saved[0]->options['images'])->toBe([['image' => null, 'link' => '/one']])
        ->and($store->savedWith)->toBe([1, 'default', 'en']);
});

it('keeps the entries the editor did not touch', function () {
    $store = recordingStore([heroRow([
        'images' => [['link' => '/one'], ['link' => '/two']],
    ])]);

    persistUpdates($store, [
        'blocks' => [
            'section-7.images.1' => [
                'id' => 'section-7.images.1',
                'type' => 'image_carousel.images.item',
                'properties' => ['link' => '/changed'],
                'children' => [],
            ],
        ],
        'regions' => contentRegion(['section-7']),
        'changes' => ['added' => [], 'updated' => ['section-7.images.1'], 'removed' => []],
    ]);

    expect($store->saved[0]->options['images'])->toBe([
        ['image' => null, 'link' => '/one'],
        ['image' => null, 'link' => '/changed'],
    ]);
});

it('drops an entry the editor removed', function () {
    $store = recordingStore([heroRow([
        'images' => [['link' => '/one'], ['link' => '/two']],
    ])]);

    persistUpdates($store, [
        'blocks' => [
            'section-7' => [
                'id' => 'section-7',
                'type' => 'image_carousel',
                'properties' => [],
                'children' => ['section-7.images.0'],
            ],
        ],
        'regions' => contentRegion(['section-7']),
        'changes' => ['added' => [], 'updated' => [], 'removed' => ['section-7.images.1']],
    ]);

    expect($store->saved[0]->options['images'])->toBe([['image' => null, 'link' => '/one']]);
});

it('stores a section the editor added', function () {
    $store = recordingStore([heroRow()]);

    persistUpdates($store, [
        'blocks' => [
            'fresh' => [
                'id' => 'fresh',
                'type' => 'image_carousel',
                'properties' => ['title' => 'Promo'],
                'children' => [],
            ],
        ],
        'regions' => contentRegion(['section-7', 'fresh']),
        'changes' => ['added' => ['fresh'], 'updated' => [], 'removed' => []],
    ]);

    expect(collect($store->saved)->map(fn (SectionDraft $d) => [$d->blockId, $d->rowId])->all())
        ->toBe([['section-7', 7], ['fresh', null]]);
});

it('takes the order from the regions the editor reports', function () {
    $store = recordingStore([
        heroRow(),
        new SectionRecord(8, 'image_carousel', 'Promo', true, 2, []),
    ]);

    persistUpdates($store, [
        'blocks' => [],
        'regions' => contentRegion(['section-8', 'section-7']),
        'changes' => ['added' => [], 'updated' => [], 'removed' => []],
    ]);

    expect(collect($store->saved)->map(fn (SectionDraft $d) => [$d->rowId, $d->sortOrder])->all())
        ->toBe([[8, 1], [7, 2]]);
});

it('falls back to the stored arrangement when the editor reports no regions', function () {
    $store = recordingStore([heroRow()]);

    persistUpdates($store, [
        'blocks' => [],
        'regions' => [],
        'changes' => ['added' => [], 'updated' => [], 'removed' => []],
    ]);

    expect(collect($store->saved)->pluck('rowId')->all())->toBe([7]);
});

it('carries over an option no section type declares', function () {
    $store = recordingStore([heroRow(['title' => 'Summer', 'legacy' => 'kept'])]);

    persistUpdates($store, [
        'blocks' => [],
        'regions' => contentRegion(['section-7']),
        'changes' => ['added' => [], 'updated' => [], 'removed' => []],
    ]);

    expect($store->saved[0]->options['legacy'])->toBe('kept');
});

it('hands the store the options each row was holding, so an untouched one is left alone', function () {
    $store = recordingStore([heroRow(['title' => 'Summer'])]);

    persistUpdates($store, [
        'blocks' => [],
        'regions' => contentRegion(['section-7']),
        'changes' => ['added' => [], 'updated' => [], 'removed' => []],
    ]);

    expect($store->previous)->toBe([7 => ['title' => 'Summer']])
        ->and($store->saved[0]->options)->toBe($store->previous[7]);
});
