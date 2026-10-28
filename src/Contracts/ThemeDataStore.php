<?php

namespace BagistoPlus\Visual\Contracts;

/**
 * Where the editor's writes to theme data go, and where it is read from.
 *
 * Reads do not go through here: theme data is always read from paths, by the
 * view finder, Craftile and the editor data store alike. A store decides which
 * paths those are, and what writing, publishing and discarding do to what they
 * serve.
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

    /**
     * The directory every theme's data is read from, for a storage scope.
     */
    public function themesPath(string $scope): string;

    /**
     * Remove every theme's data, in every scope.
     */
    public function clear(): void;
}
