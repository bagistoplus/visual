<?php

namespace BagistoPlus\Visual\Http\Controllers\Admin;

use BagistoPlus\Visual\Http\Controllers\Controller;
use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Support\Collection;

class ThemesController extends Controller
{
    /**
     * Bagisto's catalog of themes, which knows the author, version and
     * description of the themes Bagisto ships. Added in 2.4.10.
     */
    protected const THEME_CATALOG = 'Webkul\Theme\ThemeCatalog';

    public function index()
    {
        $catalog = $this->catalog();

        $themes = collect(config('themes.shop', []))
            ->map(fn (array $attributes, $code) => Theme::make(['code' => $code] + $attributes))
            ->filter(fn (Theme $theme) => $theme->editorMode()->isEditable())
            ->map(fn (Theme $theme) => $this->card($theme, $catalog->get($theme->code, [])))
            ->values();

        return view()->make('visual::admin.themes.index', [
            'themes' => $themes,
        ]);
    }

    /**
     * What a theme's card shows: what its config declares, then what Bagisto's
     * catalog knows of it. The version is read from the config itself, since
     * the theme fills in a placeholder for one that was never declared.
     *
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    protected function card(Theme $theme, array $entry): array
    {
        $config = config('themes.shop.'.$theme->code, []);

        return [
            'code' => $theme->code,
            'name' => $theme->name,
            'author' => filled($theme->author) ? $theme->author : ($entry['author'] ?? null),
            'version' => $config['version'] ?? $entry['version'] ?? null,
            'description' => filled($theme->description) ? $theme->description : ($entry['description'] ?? null),
            'screenshot' => filled($theme->previewImage) ? asset($theme->previewImage) : ($entry['screenshot'] ?? null),
            'documentationUrl' => filled($theme->documentationUrl) ? $theme->documentationUrl : null,
        ];
    }

    /**
     * Bagisto's catalog entries keyed by theme code, or none on a release
     * without a catalog.
     *
     * @return Collection<string, array<string, mixed>>
     */
    protected function catalog(): Collection
    {
        if (! class_exists(self::THEME_CATALOG)) {
            return collect();
        }

        return collect(app(self::THEME_CATALOG)->all())->keyBy('code');
    }
}
