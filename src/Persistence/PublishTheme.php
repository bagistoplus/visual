<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use Illuminate\Filesystem\Filesystem;

class PublishTheme
{
    public function __construct(
        protected Filesystem $files,
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

        // Copy files to versioned directory
        foreach ($files as $file) {
            $sourcePath = $file->getPathname();
            $targetPath = $newVersionPath.'/'.$file->getRelativePathname();

            $this->files->ensureDirectoryExists(dirname($targetPath));
            $this->files->copy($sourcePath, $targetPath);
        }

        // Copy versioned directory to live path
        // We avoid relying on symlinks, which may not always behave consistently across different operating systems
        // This also allows developers to setup versions directory cleanup process
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
            \Spatie\ResponseCache\Facades\ResponseCache::clear();
        }
    }
}
