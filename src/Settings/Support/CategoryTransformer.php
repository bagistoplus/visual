<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Webkul\Category\Repositories\CategoryRepository;

class CategoryTransformer
{
    public function __invoke(?int $id)
    {
        $category = $id ? app(CategoryRepository::class)->find($id) : null;

        if (ThemeEditor::inDesignMode() && $category) {
            ThemeEditor::preloadModel('categories', $category);
        }

        return $category;
    }
}
