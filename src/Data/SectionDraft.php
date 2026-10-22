<?php

namespace BagistoPlus\Visual\Data;

/**
 * What one section the editor holds is to be left as, before any of it is
 * written.
 *
 * A section the merchant has just added has no row yet, so `rowId` is null and
 * the type and name are what a row would be created from. Options are for the
 * locale being edited and belong in `draft_options`; the status and order
 * belong in `draft_status` and `draft_sort_order`. `inLayout` says which of
 * the two lists the editor arranges it in: the page's, or every page's.
 */
final class SectionDraft
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public readonly string $blockId,
        public readonly ?int $rowId,
        public readonly string $type,
        public readonly string $name,
        public readonly array $options,
        public readonly bool $status,
        public readonly int $sortOrder,
        public readonly bool $inLayout = false,
    ) {}

    /**
     * Whether the section has yet to be stored.
     */
    public function isNew(): bool
    {
        return $this->rowId === null;
    }
}
