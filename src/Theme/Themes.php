<?php

namespace BagistoPlus\Visual\Theme;

use BagistoPlus\Visual\Events\ThemeActivated;
use Illuminate\Support\Str;
use Webkul\Theme\Themes as BagistoThemes;

class Themes extends BagistoThemes
{
    /**
     * Prepare all themes.
     *
     * @return void
     */
    public function loadThemes()
    {
        $parentThemes = [];

        $isAdmin = request()->url() && Str::contains(request()->url(), config('app.admin_url').'/');

        if ($isAdmin) {
            $themes = config('themes.admin', []);
        } else {
            $themes = config('themes.shop', []);
        }

        foreach ($themes as $code => $data) {
            $this->themes[] = Theme::make(array_merge(['code' => $code], $data));

            if (! empty($data['parent'])) {
                $parentThemes[$code] = $data['parent'];
            }
        }

        foreach ($parentThemes as $childCode => $parentCode) {
            $child = $this->find($childCode);

            if ($this->exists($parentCode)) {
                $parent = $this->find($parentCode);
            } else {
                $parent = Theme::make(['code' => $parentCode]);
            }

            $child->setParent($parent);
        }
    }

    /**
     * Enable theme
     *
     * @param  string  $themeName
     * @return \Webkul\Theme\Theme
     */
    public function set($themeName)
    {
        /** @var Theme */
        $theme = parent::set($themeName);

        if (! $this->activeTheme instanceof Theme) {
            return $theme;
        }

        if ($this->activeTheme->isVisualTheme()) {
            app('view')->prependNamespace('shop', __DIR__.'/../../resources/views/theme');
            app('view')->prependNamespace('shop', $this->activeTheme->basePath.'/resources/views');

            ThemeActivated::dispatch($theme);
        } elseif ($this->activeTheme->editorMode()->isBagisto()) {
            // A theme whose sections Bagisto stores draws its home page through the
            // view each section type names, rather than a hardcoded list of types.
            app('view')->prependNamespace('shop', __DIR__.'/../../resources/views/bagisto');
        }

        app(ThemeRenderingSetup::class)->prepare($this->activeTheme);

        return $theme;
    }
}
