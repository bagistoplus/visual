<?php

namespace BagistoPlus\Visual\Data;

use Closure;

/**
 * A section type as native mode needs it, whichever Bagisto described it.
 *
 * Bagisto 2.5 grew a `SectionType` class per type, which 2.5.0-beta2 does not
 * have. Both are read into this shape so nothing downstream has to know which
 * one it came from.
 *
 * Fields are resolved on demand: a product carousel builds its filter options
 * from the catalogue, which listing the types a theme offers must not pay for.
 *
 * The view is where the storefront draws the section. Bagisto has no say in
 * this yet, so a type that does not name one is drawn by `shop::sections.{code}`.
 */
final class NativeSectionType
{
    /**
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $fields = null;

    /**
     * @param  Closure(): array<int, array<string, mixed>>  $fieldsResolver
     */
    public function __construct(
        public readonly string $code,
        public readonly string $title,
        public readonly string $icon,
        public readonly bool $singleton,
        public readonly bool $pinned,
        public readonly bool $rendersInLayout,
        public readonly string $view,
        private readonly Closure $fieldsResolver,
    ) {}

    /**
     * The fields the type is edited with, as `SectionSchema` describes them.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fields(): array
    {
        return $this->fields ??= ($this->fieldsResolver)();
    }
}
