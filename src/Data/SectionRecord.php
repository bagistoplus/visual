<?php

namespace BagistoPlus\Visual\Data;

/**
 * One `theme_sections` row as the editor reads it, with whatever draft it is
 * holding already resolved and its options taken for one locale.
 *
 * Keeping the rows behind a plain record lets the tree be built, and tested,
 * without a database.
 */
final class SectionRecord
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $name,
        public readonly bool $status,
        public readonly int $sortOrder,
        public readonly array $options = [],
    ) {}
}
