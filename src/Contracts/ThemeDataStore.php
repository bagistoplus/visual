<?php

namespace BagistoPlus\Visual\Contracts;

/**
 * Where the editor's writes to theme data go.
 *
 * Reads do not go through here: theme data is always read from paths, by the
 * view finder, Craftile and the editor data store alike. A store only decides
 * what writing, publishing and discarding do to what those paths serve.
 *
 * Documents are addressed by theme and by a path relative to the editor root,
 * `{channel}/{locale}/{logicalPath}`.
 */
interface ThemeDataStore
{
    /**
     * Store an editor document and mark the theme as holding unpublished edits.
     */
    public function put(string $theme, string $relativePath, string $contents): void;

    /**
     * Remove an editor document and mark the theme as holding unpublished edits.
     */
    public function delete(string $theme, string $relativePath): void;

    /**
     * Make the given documents what the storefront serves, keeping the
     * published set as a version.
     *
     * @param  array<string, string>  $documents  Contents keyed by relative path.
     */
    public function publish(string $theme, array $documents): void;

    /**
     * Replace the editor documents with what is published.
     */
    public function discard(string $theme): void;

    public function hasUnpublishedEdits(string $theme): bool;
}
