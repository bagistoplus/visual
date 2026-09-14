<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Data\SectionDraft;
use BagistoPlus\Visual\Data\SectionRecord;
use Illuminate\Support\Collection;

/**
 * Reads and writes `theme_sections` through Bagisto's own repository.
 *
 * Deliberately thin. Everything that decides what a row should hold is worked
 * out by `SectionPageBuilder` and `SectionDraftBuilder`, which need no database
 * and are tested without one; this only carries the result across. Writes go
 * through Bagisto's repository rather than the models, so a section saved here
 * is sanitized, shaped and drafted exactly as one saved in Bagisto's own
 * appearance editor.
 *
 * The repository is resolved by name because it only exists on the releases
 * native mode runs on.
 */
class SectionStore
{
    protected const REPOSITORY = 'Webkul\Theme\Repositories\SectionRepository';

    /**
     * Every section of a channel's theme, in the order it holds them, each
     * resolved to the draft it is holding.
     *
     * Sections that are switched off are kept, so that the editor can show them
     * and switch them back on.
     *
     * @return Collection<int, SectionRecord>
     */
    public function records(int $channelId, string $themeCode, string $locale): Collection
    {
        $sections = $this->repository()->findWhere([
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
        ]);

        return collect($sections)
            ->map(fn (mixed $section) => $this->record($section, $locale))
            ->sortBy(fn (SectionRecord $record) => $record->sortOrder)
            ->values();
    }

    /**
     * Hold what the editor arranged, until it is published.
     *
     * A section the merchant added is stored at once, switched off live and on
     * in draft, which is where Bagisto's own editor leaves a new one. A section
     * they removed is deleted at once, which is also what Bagisto does: there
     * is no draft that remembers a deletion, so discarding will not bring it
     * back.
     *
     * @param  Collection<int, SectionDraft>  $drafts
     */
    public function saveDrafts(Collection $drafts, int $channelId, string $themeCode, string $locale): void
    {
        $repository = $this->repository();
        $rowIds = [];

        foreach ($drafts as $draft) {
            $rowId = $draft->isNew()
                ? $this->create($draft, $channelId, $themeCode)
                : $draft->rowId;

            $repository->saveDraft($rowId, $locale, $draft->options);
            $repository->saveStatusDraft($rowId, $draft->status);

            $rowIds[] = $rowId;
        }

        $this->deleteMissing($channelId, $themeCode, $rowIds);

        $repository->saveOrderDraft($rowIds);
    }

    /**
     * Store a section the merchant added, and answer the row it was given.
     */
    protected function create(SectionDraft $draft, int $channelId, string $themeCode): int
    {
        $section = $this->repository()->create([
            'name' => $draft->name,
            'type' => $draft->type,
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
            'sort_order' => $draft->sortOrder,
            'status' => 0,
            'draft_status' => true,
        ]);

        return (int) $section->id;
    }

    /**
     * Delete the sections of a channel's theme the page no longer holds.
     *
     * @param  array<int, int>  $keep
     */
    protected function deleteMissing(int $channelId, string $themeCode, array $keep): void
    {
        $repository = $this->repository();

        $stored = collect($repository->findWhere([
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
        ]))->map(fn (mixed $section) => (int) $section->id);

        foreach ($stored->diff($keep) as $rowId) {
            $repository->delete($rowId);
        }
    }

    /**
     * Give the storefront every change a channel's theme is holding.
     */
    public function publish(int $channelId, string $themeCode): void
    {
        $repository = $this->repository();

        $repository->publishDrafts($repository->draftedSections($channelId, $themeCode));
    }

    /**
     * Throw away every change a channel's theme is holding.
     */
    public function discard(int $channelId, string $themeCode): void
    {
        $repository = $this->repository();

        $repository->discardDrafts($repository->draftedSections($channelId, $themeCode));
    }

    /**
     * One row as the editor reads it, with its draft preferred over what the
     * storefront is currently given.
     */
    protected function record(mixed $section, string $locale): SectionRecord
    {
        $translation = $section->translate($locale);

        $options = [];

        if ($translation) {
            $options = $translation->draft_options ?? $translation->options ?? [];
        }

        return new SectionRecord(
            id: (int) $section->id,
            type: (string) $section->type,
            name: (string) $section->name,
            status: (bool) ($section->draft_status ?? $section->status),
            sortOrder: (int) ($section->draft_sort_order ?? $section->sort_order),
            options: is_array($options) ? $options : [],
        );
    }

    protected function repository(): mixed
    {
        return app(self::REPOSITORY);
    }
}
