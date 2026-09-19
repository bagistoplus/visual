<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\ResolvedModelCache;
use Illuminate\Support\Collection;
use Webkul\Category\Repositories\CategoryRepository;

class CategoryListTransformer implements SettingTransformerInterface
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
            $cached = $cache->getCategory($id);

            if ($cached) {
                $resolved[$id] = $cached;
            } else {
                $missing[] = $id;
            }
        }

        if ($missing !== []) {
            $categories = app(CategoryRepository::class)
                ->withCount('products')
                ->findWhereIn('id', $missing);

            foreach ($categories as $category) {
                $id = (int) $category->id;

                $resolved[$id] = $category;
                $cache->rememberCategory($id, fn () => $category);
            }
        }

        $inDesignMode = ThemeEditor::inDesignMode();

        return Collection::make($value)
            ->map(function ($id) use ($resolved, $inDesignMode) {
                $category = $resolved[(int) $id] ?? null;

                if (! $category) {
                    return null;
                }

                if ($inDesignMode) {
                    ThemeEditor::preloadModel('categories', $category);
                }

                return $category;
            })
            ->filter()
            ->values();
    }
}
