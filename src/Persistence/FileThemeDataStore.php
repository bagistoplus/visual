<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\ThemePathsResolver;
use Illuminate\Filesystem\Filesystem;

/**
 * Theme data kept in files under `data_path`, which is also where it is read.
 *
 * Each theme holds an `editor` tree, a `live` tree and `versions/V{time}`
 * snapshots. The editor tree carries a `.last-edit` marker while it holds
 * changes the live tree does not.
 */
class FileThemeDataStore implements ThemeDataStore
{
    public function __construct(
        protected ThemePathsResolver $themePathsResolver,
        protected Filesystem $files,
    ) {}

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
        $newVersionPath = $this->themePathsResolver->getThemeBaseDataPath($theme, 'versions/V'.time());
        $livePath = $this->themePathsResolver->getThemeBaseDataPath($theme, 'live');

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
        $editorPath = $this->themePathsResolver->getThemeBaseDataPath($theme, 'editor');
        $livePath = $this->themePathsResolver->getThemeBaseDataPath($theme, 'live');

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

    protected function markEdited(string $theme): void
    {
        $path = $this->lastEditPath($theme);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, (string) time());
    }

    protected function pruneVersions(string $theme): void
    {
        $keep = max(0, (int) config('bagisto_visual.versions_to_keep', 5));
        $versionsPath = $this->themePathsResolver->getThemeBaseDataPath($theme, 'versions');

        $versions = collect($this->files->directories($versionsPath))
            ->filter(fn (string $path) => preg_match('/^V\d+$/', basename($path)) === 1)
            ->sortByDesc(fn (string $path) => (int) substr(basename($path), 1))
            ->values();

        foreach ($versions->slice($keep) as $path) {
            $this->files->deleteDirectory($path);
        }
    }

    protected function editorPath(string $theme, string $relativePath): string
    {
        return $this->themePathsResolver->getThemeBaseDataPath($theme, 'editor').'/'.ltrim($relativePath, '/');
    }

    protected function lastEditPath(string $theme): string
    {
        return $this->themePathsResolver->getThemeBaseDataPath($theme, 'editor/.last-edit');
    }
}
