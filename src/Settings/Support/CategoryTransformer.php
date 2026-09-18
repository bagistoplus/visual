<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\ResolvedModelCache;
use Webkul\Category\Repositories\CategoryRepository;

class CategoryTransformer
{
    public function __invoke(?int $id, array $schema = [])
    {
        if (! $id) {
            return null;
        }

        $category = app(ResolvedModelCache::class)->rememberCategory(
            $id,
            fn () => app(CategoryRepository::class)->find($id)
        );

        if (ThemeEditor::inDesignMode() && $category) {
            ThemeEditor::preloadModel('categories', $category);
        }

        return $category;
    }
}
