<?php

namespace BagistoPlus\Visual\Theme;

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

        if (Str::contains(request()->url(), config('app.admin_url').'/')) {
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
        $theme = parent::set($themeName);

        if ($this->activeTheme instanceof Theme && $this->activeTheme->isVisualTheme()) {
            app('view')->prependNamespace('shop', __DIR__.'/../../resources/views/theme');
        }

        return $theme;
    }
}
