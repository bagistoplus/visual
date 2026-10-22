<?php

use BagistoPlus\Visual\Data\SectionDraft;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Support\SectionOptions;

/**
 * A repository holding rows by id and live position, recording the order it is
 * handed.
 */
function orderedRows(array $sortOrders): object
{
    return new class($sortOrders)
    {
        public ?array $order = null;

        public array $created = [];

        public function __construct(public array $sortOrders) {}

        public function create(array $attributes): object
        {
            $id = max(array_keys($this->sortOrders)) + 1;
            $this->created[$id] = $attributes['sort_order'];

            return (object) ['id' => $id];
        }

        public function findWhere(array $where): array
        {
            return collect($this->sortOrders)
                ->map(fn (int $sortOrder, int $id) => (object) ['id' => $id, 'sort_order' => $sortOrder])
                ->values()
                ->all();
        }

        public function saveDraft(int $id, string $locale, array $options): void {}

        public function saveStatusDraft(int $id, bool $status): void {}

        public function delete(int $id): void {}

        public function saveOrderDraft(array $ids): void
        {
            $this->order = $ids;
        }
    };
}

function orderingStore(object $repository): SectionStore
{
    return new class(new SectionOptions, $repository) extends SectionStore
    {
        public function __construct(SectionOptions $options, protected object $fake)
        {
            parent::__construct($options);
        }

        protected function repository(): mixed
        {
            return $this->fake;
        }
    };
}

function draftFor(int $rowId, bool $inLayout = false): SectionDraft
{
    return new SectionDraft('section-'.$rowId, $rowId, 'static_content', 'Section', [], true, 0, $inLayout);
}

it('hands bagisto the order it holds when nothing on the page moved', function () {
    // Bagisto appended section 14 after the layout's services, 11.
    $repository = orderedRows([1 => 1, 2 => 2, 11 => 3, 14 => 4, 13 => 5]);

    orderingStore($repository)->saveDrafts(
        collect([draftFor(1), draftFor(2), draftFor(14), draftFor(11, true), draftFor(13, true)]),
        1, 'default', 'en',
        [1 => [], 2 => [], 14 => [], 11 => [], 13 => []],
    );

    expect($repository->order)->toBe([1, 2, 11, 14, 13]);
});

it('stores a section the merchant added after every other, moving none', function () {
    $repository = orderedRows([1 => 1, 2 => 2, 11 => 3, 13 => 4]);
    $added = new SectionDraft('new-block', null, 'static_content', 'Added', [], true, 0);

    orderingStore($repository)->saveDrafts(
        collect([draftFor(1), $added, draftFor(2), draftFor(11, true), draftFor(13, true)]),
        1, 'default', 'en',
        [1 => [], 2 => [], 11 => [], 13 => []],
    );

    expect($repository->created)->toBe([14 => 5])
        ->and($repository->order)->toBe([1, 14, 11, 13, 2]);
});

it('hands bagisto a section the merchant moved where they moved it', function () {
    $repository = orderedRows([1 => 1, 2 => 2, 11 => 3, 14 => 4, 13 => 5]);

    orderingStore($repository)->saveDrafts(
        collect([draftFor(14), draftFor(1), draftFor(2), draftFor(11, true), draftFor(13, true)]),
        1, 'default', 'en',
        [1 => [], 2 => [], 14 => [], 11 => [], 13 => []],
    );

    expect($repository->order)->toBe([14, 1, 11, 2, 13]);
});
