<?php

namespace BagistoPlus\Visual\Contracts;

use BagistoPlus\Visual\Data\ThemeSnapshot;

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
     * The codes of the themes this store holds data for.
     *
     * @return array<int, string>
     */
    public function themes(): array;

    /**
     * Everything this store holds for a theme.
     */
    public function snapshot(string $theme): ThemeSnapshot;

    /**
     * Make a snapshot what this store holds for a theme.
     *
     * Its live documents are published as a new version, so the versions
     * already held stay. The editor documents are replaced as a whole. A
     * snapshot of a theme that was never published leaves what is live alone.
     */
    public function restore(string $theme, ThemeSnapshot $snapshot): void;

    /**
     * The directory every theme's data is read from, for a storage scope.
     */
    public function themesPath(string $scope): string;

    /**
     * Remove every theme's data, in every scope.
     */
    public function clear(): void;
}
