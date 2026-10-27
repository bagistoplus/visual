<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Facades\ThemePathsResolver;
use Illuminate\Filesystem\Filesystem;
use Spatie\ResponseCache\Facades\ResponseCache;

class PublishTheme
{
    public function __construct(
        protected Filesystem $files,
        protected EditorDataStore $editorDataStore,
        protected ThemeDataStore $dataStore,
        protected ThemeSettingsCache $settingsCache,
    ) {}

    /**
     * Publish the editor data, with every document resolved against the
     * documents it inherits from, so the storefront needs no parent to read it.
     */
    public function handle(string $themeCode): void
    {
        $editorPath = ThemePathsResolver::getThemeBaseDataPath($themeCode, 'editor');

        // A theme whose sections Bagisto stores holds nothing here until its
        // settings are first edited, and there is nothing to publish until then.
        if (! $this->files->exists($editorPath)) {
            return;
        }

        $documents = collect($this->files->allFiles($editorPath))
            ->filter(fn ($file) => $file->getFilename() !== '.last-edit')
            ->mapWithKeys(function ($file) use ($themeCode) {
                $relativePath = str_replace('\\', '/', $file->getRelativePathname());

                if ($file->getExtension() !== 'json') {
                    return [$relativePath => $this->files->get($file->getPathname())];
                }

                $data = $this->editorDataStore->loadResolved($themeCode, $relativePath);
                unset($data['parent']);

                return [$relativePath => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)];
            })
            ->all();

        $this->dataStore->publish($themeCode, $documents);

        // The storefront reads settings through a cache of its own, which is
        // now holding what was published before this.
        $this->settingsCache->forgetAll($themeCode);

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
