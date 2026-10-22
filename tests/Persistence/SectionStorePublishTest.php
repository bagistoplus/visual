<?php

use BagistoPlus\Visual\Data\SectionDraft;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Support\SectionOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

function storeOver(object $repository): SectionStore
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

function sectionRows(array $ids): object
{
    return new class($ids)
    {
        public array $calls = [];

        public function __construct(public array $ids) {}

        public function draftedSections(int $channelId, string $themeCode): Collection
        {
            return collect($this->ids)->map(fn (int $id) => (object) ['id' => $id]);
        }

        public function publishDrafts(Collection $sections): Collection
        {
            $this->calls[] = 'publish';

            return $sections->map(fn (object $section) => (object) ['id' => $section->id, 'published' => true]);
        }

        public function findWhere(array $where): array
        {
            return array_map(fn (int $id) => (object) ['id' => $id], $this->ids);
        }

        public function delete(int $id): void
        {
            $this->calls[] = "delete {$id}";
        }

        public function saveDraft(int $id, string $locale, array $options): void {}

        public function saveStatusDraft(int $id, bool $status): void {}

        public function saveOrderDraft(array $ids): void {}
    };
}

it('tells bagisto about every section it publishes, so its page cache forgets them', function () {
    Event::fake();

    $repository = sectionRows([4, 7]);

    storeOver($repository)->publish(1, 'default');

    expect($repository->calls)->toBe(['publish']);

    Event::assertDispatched('section.update.before', fn ($event, $id) => $id === 4);
    Event::assertDispatched('section.update.before', fn ($event, $id) => $id === 7);
    Event::assertDispatched('section.update.after', fn ($event, $section) => $section->id === 4 && $section->published);
    Event::assertDispatched('section.update.after', fn ($event, $section) => $section->id === 7 && $section->published);
});

it('tells bagisto about a section it deletes while the row is still there to read', function () {
    $repository = sectionRows([4, 7]);
    $order = [];

    Event::listen('section.delete.before', function ($id) use (&$order, $repository) {
        $order[] = "before {$id}, ".count($repository->calls).' deleted';
    });
    Event::listen('section.delete.after', function ($id) use (&$order) {
        $order[] = "after {$id}";
    });

    $kept = new SectionDraft('hero', 4, 'image_carousel', 'Hero', [], true, 0);

    storeOver($repository)->saveDrafts(collect([$kept]), 1, 'default', 'en', [4 => []]);

    expect($repository->calls)->toBe(['delete 7'])
        ->and($order)->toBe(['before 7, 0 deleted', 'after 7']);
});
