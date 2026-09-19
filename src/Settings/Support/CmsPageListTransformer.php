<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\ResolvedModelCache;
use Illuminate\Support\Collection;
use Webkul\CMS\Repositories\PageRepository;

class CmsPageListTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, array $schema = []): mixed
    {
        if (! is_array($value) || empty($value)) {
            return collect();
        }

        $cache = app(ResolvedModelCache::class);

        $resolved = [];
        $missing = [];

        foreach (array_unique(array_map('intval', $value)) as $id) {
            $cached = $cache->getCmsPage($id);

            if ($cached) {
                $resolved[$id] = $cached;
            } else {
                $missing[] = $id;
            }
        }

        if ($missing !== []) {
            $pages = app(PageRepository::class)->findWhereIn('id', $missing);

            foreach ($pages as $page) {
                $id = (int) $page->id;

                $resolved[$id] = $page;
                $cache->rememberCmsPage($id, fn () => $page);
            }
        }

        $inDesignMode = ThemeEditor::inDesignMode();

        return Collection::make($value)
            ->map(function ($id) use ($resolved, $inDesignMode) {
                $page = $resolved[(int) $id] ?? null;

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
