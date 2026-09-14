<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Data\SectionRecord;
use BagistoPlus\Visual\Support\SectionDraftBuilder;
use BagistoPlus\Visual\Support\SectionPageBuilder;
use Craftile\Laravel\Data\UpdateRequest;

/**
 * Holds what the editor changed about a Bagisto theme's sections.
 *
 * The editor reports only the blocks it changed, so the page it was working on
 * is read back from the rows first and the change laid over it. What comes out
 * is a whole page, which is what rebuilding a section's options needs: an
 * option holds every entry of a list, not the one that moved.
 */
class PersistSectionUpdates
{
    public function __construct(
        protected SectionStore $store,
        protected SectionPageBuilder $pageBuilder,
        protected SectionDraftBuilder $draftBuilder,
    ) {}

    public function handle(
        UpdateRequest $updates,
        int $channelId,
        string $themeCode,
        string $locale
    ): void {
        $records = $this->store->records($channelId, $themeCode, $locale);
        $page = $this->pageBuilder->build($records, $themeCode);
        $previous = $this->optionsByRow($records);

        $drafts = $this->draftBuilder->build(
            blocks: $this->apply($page['blocks'], $updates),
            regions: $this->regions($page['regions'], $updates->regions),
            existingOptions: $previous,
            themeCode: $themeCode,
        );

        $this->store->saveDrafts($drafts, $channelId, $themeCode, $locale, $previous);
    }

    /**
     * The regions as the editor left them, laid over the ones that were stored.
     *
     * A page that is not the home page carries the layout sections alone, so
     * what it reports has to be read as a change to those and not as a page
     * whose content sections are all gone.
     *
     * @param  array<int, array<string, mixed>>  $stored
     * @param  array<int, array<string, mixed>>  $reported
     * @return array<int, array<string, mixed>>
     */
    protected function regions(array $stored, array $reported): array
    {
        if ($reported === []) {
            return $stored;
        }

        $byName = [];

        foreach ($stored as $region) {
            $byName[$region['name']] = $region;
        }

        foreach ($reported as $region) {
            $byName[$region['name']] = $region;
        }

        return array_values($byName);
    }

    /**
     * The page as the editor left it: what it changed laid over what was
     * stored, less what it removed.
     *
     * @param  array<string, array<string, mixed>>  $blocks
     * @return array<string, array<string, mixed>>
     */
    protected function apply(array $blocks, UpdateRequest $updates): array
    {
        $blocks = array_replace($blocks, $updates->getBlocks());

        foreach ($updates->getRemovedBlocks() as $blockId) {
            unset($blocks[$blockId]);
        }

        return $blocks;
    }

    /**
     * What each row holds today, so that an option no section type declares is
     * carried over rather than dropped.
     *
     * @param  iterable<SectionRecord>  $records
     * @return array<int, array<string, mixed>>
     */
    protected function optionsByRow(iterable $records): array
    {
        $options = [];

        foreach ($records as $record) {
            $options[$record->id] = $record->options;
        }

        return $options;
    }
}
