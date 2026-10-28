<?php

namespace BagistoPlus\Visual\Data;

/**
 * Everything a store holds for one theme, as it would be copied to another
 * store: the editor documents, the published ones, and whether the editor
 * holds changes that are not published.
 *
 * `live` is null for a theme that was never published.
 */
final class ThemeSnapshot
{
    /**
     * @param  array<string, string>  $editor  Contents keyed by relative path.
     * @param  array<string, string>|null  $live  Contents keyed by relative path.
     */
    public function __construct(
        public readonly array $editor,
        public readonly ?array $live,
        public readonly bool $edited,
    ) {}
}
