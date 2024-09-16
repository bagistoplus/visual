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
            $this->themes[] = $this->createTheme(array_merge(['code' => $code], $data));

            if (! empty($data['parent'])) {
                $parentThemes[$code] = $data['parent'];
            }
        }

        foreach ($parentThemes as $childCode => $parentCode) {
            $child = $this->find($childCode);

            if ($this->exists($parentCode)) {
                $parent = $this->find($parentCode);
            } else {
                $parent = $this->createTheme(['code' => $parentCode]);
            }

            $child->setParent($parent);
        }
    }

    public function createTheme(array $data)
    {
        return new Theme(
            $data['code'],
            $data['name'] ?? $data['code'],
            $data['assets_path'] ?? $data['code'],
            $data['views_path'] ?? $data['code'],
            $data['vite'] ?? []
        );
    }
}
