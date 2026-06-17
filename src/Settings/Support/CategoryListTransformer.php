<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use Illuminate\Support\Collection;
use Webkul\Category\Repositories\CategoryRepository;

class CategoryListTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, array $schema = []): mixed
    {
        if (! is_array($value) || empty($value)) {
            return collect();
        }

        $categories = app(CategoryRepository::class)
            ->getModel()
            ->whereIn('id', $value)
            ->withCount('products')
            ->get()
            ->keyBy('id');

        $inDesignMode = ThemeEditor::inDesignMode();

        return Collection::make($value)
            ->map(function ($id) use ($categories, $inDesignMode) {
                $category = $categories->get($id);

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
