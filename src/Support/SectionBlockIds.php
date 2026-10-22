<?php

namespace BagistoPlus\Visual\Support;

/**
 * The ids the editor holds a Bagisto section's blocks under.
 *
 * A section is addressed by the row it is stored as, and everything beneath it
 * by the path from that row: the option key of a repeating list, then the
 * position of an entry within it.
 *
 *     section-7                the row
 *     section-7.images         the list stored under "images"
 *     section-7.images.1       its second entry
 *
 * Positions are derived from the order entries are held in, never stored, so an
 * entry's id changes when the list is reordered.
 */
class SectionBlockIds
{
    public const SEPARATOR = '.';

    protected const PREFIX = 'section-';

    /**
     * The id a section row is held under.
     */
    public function section(int $rowId): string
    {
        return self::PREFIX.$rowId;
    }

    /**
     * The row a section block stands for, or null when the id names something
     * else, such as a list or an entry.
     */
    public function rowId(string $blockId): ?int
    {
        if (! str_starts_with($blockId, self::PREFIX) || str_contains($blockId, self::SEPARATOR)) {
            return null;
        }

        $rowId = substr($blockId, strlen(self::PREFIX));

        return ctype_digit($rowId) ? (int) $rowId : null;
    }

    /**
     * The id of something held beneath another block.
     */
    public function child(string $parentId, string|int $part): string
    {
        return $parentId.self::SEPARATOR.$part;
    }
}
