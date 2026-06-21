<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use Illuminate\Filesystem\Filesystem;
use Spatie\ResponseCache\Facades\ResponseCache;

class PublishTheme
{
    public function __construct(
        protected Filesystem $files,
        protected EditorDataStore $editorDataStore,
    ) {}

    /**
     * Publish the theme version to the live path.
     *
     * Creates a versioned backup and copies to live directory.
     */
    public function handle(string $themeCode): void
    {
        $editorPath = ThemePathsResolver::getThemeBaseDataPath($themeCode, 'editor');
        $newVersionPath = ThemePathsResolver::getThemeBaseDataPath($themeCode, 'versions/V'.time());
        $livePath = ThemePathsResolver::getThemeBaseDataPath($themeCode, 'live');

        // Get all files except .last-edit
        $files = collect($this->files->allFiles($editorPath))
            ->filter(fn ($file) => $file->getFilename() !== '.last-edit');

        // Copy resolved editor files to versioned directory
        foreach ($files as $file) {
            $sourcePath = $file->getPathname();
            $relativePath = $file->getRelativePathname();
            $targetPath = $newVersionPath.'/'.$relativePath;

            $this->files->ensureDirectoryExists(dirname($targetPath));

            if ($file->getExtension() === 'json') {
                $data = $this->editorDataStore->loadResolved($themeCode, $relativePath);
                unset($data['parent']);
                $this->files->put($targetPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            } else {
                $this->files->copy($sourcePath, $targetPath);
            }
        }

        // Copy versioned directory to live path
        // We avoid relying on symlinks, which may not always behave consistently across different operating systems
        // This also allows developers to setup versions directory cleanup process
        if ($this->files->exists($livePath)) {
            $this->files->deleteDirectory($livePath);
        }

        $this->files->copyDirectory($newVersionPath, $livePath);

        // Remove last edit marker - all edits are now published
        $lastEditFile = ThemePathsResolver::getThemeBaseDataPath($themeCode, 'editor/.last-edit');
        if ($this->files->exists($lastEditFile)) {
            $this->files->delete($lastEditFile);
        }

        // Clear response cache if available
        $this->clearResponseCache();
    }

    /**
     * Clear response cache if Spatie ResponseCache is installed.
     */
    protected function clearResponseCache(): void
    {
        if (class_exists('\\Spatie\\ResponseCache\\Facades\\ResponseCache')) {
            ResponseCache::clear();
        }
    }
}
