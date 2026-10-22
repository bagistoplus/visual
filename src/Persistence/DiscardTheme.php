<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use Illuminate\Filesystem\Filesystem;

/**
 * Throw away every unpublished change a theme is holding in files.
 *
 * The published data is copied back over the editor's, rather than the editor's
 * being deleted: nothing falls back from one to the other, so an editor left
 * empty would show the theme's defaults while the storefront still served what
 * was published.
 *
 * Used for themes whose sections Bagisto stores, where the editor offers a
 * discard button in place of undo. A visual theme has undo instead and never
 * reaches this.
 */
class DiscardTheme
{
    public function __construct(
        protected Filesystem $files,
        protected ThemeSettingsCache $settingsCache,
    ) {}

    public function handle(string $themeCode): void
    {
        $editorPath = ThemePathsResolver::getThemeBaseDataPath($themeCode, 'editor');
        $livePath = ThemePathsResolver::getThemeBaseDataPath($themeCode, 'live');

        if ($this->files->exists($editorPath)) {
            $this->files->deleteDirectory($editorPath);
        }

        if ($this->files->exists($livePath)) {
            $this->files->copyDirectory($livePath, $editorPath);
        }

        $this->settingsCache->forgetAll($themeCode);
    }
}
