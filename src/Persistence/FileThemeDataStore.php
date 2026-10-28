<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Data\ThemeSnapshot;
use BagistoPlus\Visual\Facades\Visual;
use Illuminate\Filesystem\Filesystem;

/**
 * Theme data kept in files under the file storage path, which is also where it is read.
 *
 * Each theme holds an `editor` tree, a `live` tree and `versions/V{time}`
 * snapshots. The editor tree carries a `.last-edit` marker while it holds
 * changes the live tree does not.
 */
class FileThemeDataStore implements ThemeDataStore
{
    public function __construct(protected Filesystem $files) {}

    public function put(string $theme, string $relativePath, string $contents): void
    {
        $path = $this->editorPath($theme, $relativePath);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $contents);

        $this->markEdited($theme);
    }

    public function delete(string $theme, string $relativePath): void
    {
        $path = $this->editorPath($theme, $relativePath);

        if ($this->files->exists($path)) {
            $this->files->delete($path);
        }

        $this->markEdited($theme);
    }

    /**
     * Write the documents to a new version and copy it over the live tree.
     *
     * We avoid relying on symlinks, which may not always behave consistently
     * across different operating systems. Keeping the versions as directories
     * also lets developers set up their own cleanup.
     */
    public function publish(string $theme, array $documents): void
    {
        $newVersionPath = $this->themePath($theme, 'versions/V'.now()->getTimestamp());
        $livePath = $this->themePath($theme, 'live');

        // An editor holding nothing to copy still publishes, as a version that
        // overrides nothing.
        $this->files->ensureDirectoryExists($newVersionPath);

        foreach ($documents as $relativePath => $contents) {
            $targetPath = $newVersionPath.'/'.$relativePath;

            $this->files->ensureDirectoryExists(dirname($targetPath));
            $this->files->put($targetPath, $contents);
        }

        if ($this->files->exists($livePath)) {
            $this->files->deleteDirectory($livePath);
        }

        $this->files->copyDirectory($newVersionPath, $livePath);

        $this->pruneVersions($theme);

        $lastEditFile = $this->lastEditPath($theme);

        if ($this->files->exists($lastEditFile)) {
            $this->files->delete($lastEditFile);
        }
    }

    /**
     * The published data is copied back over the editor's, rather than the
     * editor's being deleted: nothing falls back from one to the other, so an
     * editor left empty would show the theme's defaults while the storefront
     * still served what was published.
     */
    public function discard(string $theme): void
    {
        $editorPath = $this->themePath($theme, 'editor');
        $livePath = $this->themePath($theme, 'live');

        if ($this->files->exists($editorPath)) {
            $this->files->deleteDirectory($editorPath);
        }

        if ($this->files->exists($livePath)) {
            $this->files->copyDirectory($livePath, $editorPath);
        }
    }

    public function hasUnpublishedEdits(string $theme): bool
    {
        return $this->files->exists($this->lastEditPath($theme));
    }

    public function scopes(): array
    {
        $path = rtrim(self::dataPath(), '/\\');

        if (! $this->files->isDirectory($path)) {
            return [];
        }

        $scopes = collect($this->files->directories($path))
            ->map(fn (string $directory) => basename($directory))
            ->filter(fn (string $name) => $name !== 'themes'
                && Visual::isValidStorageScope($name)
                && $this->files->isDirectory("{$path}/{$name}/themes"))
            ->sort()
            ->values();

        if ($this->files->isDirectory("{$path}/themes")) {
            $scopes->prepend('');
        }

        return $scopes->all();
    }

    public function themes(): array
    {
        $path = $this->themesPath(Visual::storageScope());

        if (! $this->files->isDirectory($path)) {
            return [];
        }

        return collect($this->files->directories($path))
            ->map(fn (string $directory) => basename($directory))
            ->sort()
            ->values()
            ->all();
    }

    public function snapshot(string $theme): ThemeSnapshot
    {
        $livePath = $this->themePath($theme, 'live');

        return new ThemeSnapshot(
            editor: $this->documentsIn($this->themePath($theme, 'editor')),
            live: $this->files->isDirectory($livePath) ? $this->documentsIn($livePath) : null,
            edited: $this->hasUnpublishedEdits($theme),
        );
    }

    public function restore(string $theme, ThemeSnapshot $snapshot): void
    {
        if ($snapshot->live !== null) {
            $this->publish($theme, $snapshot->live);
        }

        $editorPath = $this->themePath($theme, 'editor');

        if ($this->files->exists($editorPath)) {
            $this->files->deleteDirectory($editorPath);
        }

        foreach ($snapshot->editor as $relativePath => $contents) {
            $path = $this->editorPath($theme, $relativePath);

            $this->files->ensureDirectoryExists(dirname($path));
            $this->files->put($path, $contents);
        }

        if ($snapshot->edited) {
            $this->markEdited($theme);
        }
    }

    public static function dataPath(): string
    {
        return (string) config('bagisto_visual.storages.file.path');
    }

    public function themesPath(string $scope): string
    {
        return rtrim(self::dataPath(), '/\\')
            .($scope === '' ? '' : '/'.$scope)
            .'/themes';
    }

    public function clear(): void
    {
        $path = self::dataPath();

        if ($this->files->isDirectory($path)) {
            $this->files->cleanDirectory($path);
        }
    }

    protected function markEdited(string $theme): void
    {
        $path = $this->lastEditPath($theme);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, (string) now()->getTimestamp());
    }

    protected function pruneVersions(string $theme): void
    {
        $keep = max(0, (int) config('bagisto_visual.versions_to_keep', 5));
        $versionsPath = $this->themePath($theme, 'versions');

        $versions = collect($this->files->directories($versionsPath))
            ->filter(fn (string $path) => preg_match('/^V\d+$/', basename($path)) === 1)
            ->sortByDesc(fn (string $path) => (int) substr(basename($path), 1))
            ->values();

        foreach ($versions->slice($keep) as $path) {
            $this->files->deleteDirectory($path);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function documentsIn(string $path): array
    {
        if (! $this->files->isDirectory($path)) {
            return [];
        }

        return collect($this->files->allFiles($path))
            ->mapWithKeys(fn ($file) => [str_replace('\\', '/', $file->getRelativePathname()) => $this->files->get($file->getPathname())])
            ->sortKeys()
            ->all();
    }

    /**
     * Built from this store's own root rather than the one the app reads from,
     * which belongs to the other store while the app uses the database and this
     * store is being copied to or from.
     */
    protected function themePath(string $theme, string $mode): string
    {
        return $this->themesPath(Visual::storageScope())."/{$theme}/{$mode}";
    }

    protected function editorPath(string $theme, string $relativePath): string
    {
        return $this->themePath($theme, 'editor').'/'.ltrim($relativePath, '/');
    }

    protected function lastEditPath(string $theme): string
    {
        return $this->themePath($theme, 'editor/.last-edit');
    }
}
