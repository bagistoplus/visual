<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use Illuminate\Support\Collection;
use Webkul\CMS\Repositories\PageRepository;

class CmsPageListTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, mixed $property = null): mixed
    {
        if (! is_array($value) || empty($value)) {
            return collect();
        }

        $pages = app(PageRepository::class)
            ->findWhereIn('id', $value)
            ->keyBy('id');

        $inDesignMode = ThemeEditor::inDesignMode();

        return Collection::make($value)
            ->map(function ($id) use ($pages, $inDesignMode) {
                $page = $pages->get($id);

                if (! $page) {
                    return null;
                }

                if ($inDesignMode) {
                    ThemeEditor::preloadModel('cms_pages', $page);
                }

                return $page;
            })
            ->filter()
            ->values();
    }
}
