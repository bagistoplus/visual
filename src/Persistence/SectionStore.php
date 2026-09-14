<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Data\SectionDraft;
use BagistoPlus\Visual\Data\SectionRecord;
use BagistoPlus\Visual\Support\SectionOptions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
 * Bagisto mode runs on.
 */
class SectionStore
{
    protected const REPOSITORY = 'Webkul\Theme\Repositories\SectionRepository';

    /**
     * Turns a stored path into the url the storefront reaches it by. Added
     * after the section system landed, so a release without it says where its
     * files are in the path itself.
     */
    protected const THEME_STORAGE = 'Webkul\Theme\ThemeStorage';

    /**
     * The request attribute Bagisto reads a drafted section through.
     */
    protected const PREVIEWING = 'appearance_previewing';

    public function __construct(protected SectionOptions $options) {}

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
     * A section whose options come back as they were is left alone. The editor
     * reports the whole page on every save, and writing a draft for each would
     * leave every section on it holding a change the merchant never made.
     *
     * @param  Collection<int, SectionDraft>  $drafts
     * @param  array<int, array<string, mixed>>  $previousOptions  Options as read, keyed by row id.
     */
    public function saveDrafts(
        Collection $drafts,
        int $channelId,
        string $themeCode,
        string $locale,
        array $previousOptions = []
    ): void {
        $repository = $this->repository();
        $rowIds = [];

        foreach ($drafts as $draft) {
            $rowId = $draft->isNew()
                ? $this->create($draft, $channelId, $themeCode)
                : $draft->rowId;

            if ($draft->isNew() || ! $this->options->same($previousOptions[$draft->rowId] ?? [], $draft->options)) {
                $repository->saveDraft($rowId, $locale, $draft->options);
            }

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
     * The sections a channel's theme draws, each resolved to the draft it is
     * holding, as Bagisto's own preview resolves them.
     *
     * Marking the request as previewing is what makes the layout draw its
     * drafted footer and services too, since it asks for those itself.
     *
     * @return iterable<mixed>
     */
    public function draftedForPreview(int $channelId, string $themeCode, string $locale): iterable
    {
        request()->attributes->set(self::PREVIEWING, true);

        return $this->repository()->getDraftedForPreview($channelId, $themeCode, $locale);
    }

    /**
     * Whether a channel's theme is holding a change the storefront has not
     * been given yet.
     */
    public function hasDrafts(int $channelId, string $themeCode): bool
    {
        return $this->countDrafts($channelId, $themeCode) > 0;
    }

    /**
     * How many of a channel's sections are holding a change the storefront has
     * not been given yet, which is what the editor counts as one edit: Bagisto
     * drafts a whole section, not a field of one.
     */
    public function countDrafts(int $channelId, string $themeCode): int
    {
        return count($this->repository()->draftedSections($channelId, $themeCode));
    }

    /**
     * Keep a file among a section's own media, and answer it as the storefront
     * reaches it.
     *
     * Bagisto files a section's uploads under the row it is stored as and
     * deletes any file its options stop naming, so a file kept here is a file
     * Bagisto's own editor lists, replaces and cleans up.
     *
     * @return array{path: string, type: string, url: ?string}
     */
    public function storeMedia(int $rowId, UploadedFile $file): array
    {
        $media = $this->repository()->storeMedia($rowId, $file);

        return array_merge($media, [
            'url' => $this->mediaUrl($media['path'] ?? null),
        ]);
    }

    /**
     * Where a stored file is served from.
     *
     * Releases disagree on what they hand back: one answers a path on the disk
     * and offers a storage object to resolve it, an earlier one answers a path
     * already rooted in the public directory and offers nothing.
     */
    protected function mediaUrl(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if ($storage = $this->themeStorage()) {
            return $storage->embedUrl($path);
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        if (Str::startsWith($path, ['storage/', '/'])) {
            return url($path);
        }

        return Storage::url($path);
    }

    /**
     * The theme a section belongs to, or null when no such section is stored.
     */
    public function themeOf(int $rowId): ?string
    {
        $section = $this->repository()->find($rowId);

        return $section?->theme_code;
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

    /**
     * What the installed Bagisto resolves a stored path with, or null on a
     * release that has no such thing.
     */
    protected function themeStorage(): ?object
    {
        return class_exists(self::THEME_STORAGE) ? app(self::THEME_STORAGE) : null;
    }

    protected function repository(): mixed
    {
        return app(self::REPOSITORY);
    }
}
