<?php

namespace BagistoPlus\Visual\Data;

/**
 * What one `theme_sections` row is to be left holding after an edit, before any
 * of it is written.
 *
 * Options are for the locale being edited and belong in `draft_options`; the
 * status and order belong in `draft_status` and `draft_sort_order`.
 */
final class SectionDraft
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public readonly int $rowId,
        public readonly array $options,
        public readonly bool $status,
        public readonly int $sortOrder,
    ) {}
}
