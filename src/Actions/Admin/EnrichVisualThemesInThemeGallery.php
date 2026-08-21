<?php

namespace BagistoPlus\Visual\Actions\Admin;

use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Bagisto's theme gallery only knows the metadata shipped in its own catalog file, so a
 * visual theme lands there as a bare card. Fill the missing fields from the theme config.
 */
class EnrichVisualThemesInThemeGallery
{
    public function __invoke(View $view): void
    {
        $themes = $view->getData()['themes'] ?? null;

        if (! is_array($themes) && ! $themes instanceof Collection) {
            return;
        }

        $view->with('themes', collect($themes)->map(fn ($theme) => $this->enrich($theme)));
    }

    /**
     * Complete a single catalog row, leaving anything that is not a visual theme alone.
     *
     * @param  mixed  $row
     * @return mixed
     */
    protected function enrich($row)
    {
        if (! is_array($row) || ! isset($row['code'])) {
            return $row;
        }

        $config = config('themes.shop.'.$row['code']);

        if (! is_array($config) || ! ($config['visual_theme'] ?? false)) {
            return $row;
        }

        $previewImage = $config['preview_image'] ?? null;

        $fallbacks = [
            'author' => $config['author'] ?? null,
            'version' => $config['version'] ?? null,
            'description' => $config['description'] ?? null,
            'screenshot' => filled($previewImage) ? asset($previewImage) : null,
        ];

        foreach ($fallbacks as $key => $value) {
            if (blank($value) || filled($row[$key] ?? null)) {
                continue;
            }

            $row[$key] = $value;
        }

        return $row;
    }
}
