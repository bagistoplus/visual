<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use Illuminate\Support\Collection;
use Webkul\Product\Repositories\ProductRepository;

class ProductListTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, array $schema = []): mixed
    {
        if (! is_array($value) || empty($value)) {
            return collect();
        }

        $products = app(ProductRepository::class)
            ->findWhereIn('id', $value)
            ->keyBy('id');

        $inDesignMode = ThemeEditor::inDesignMode();

        return Collection::make($value)
            ->map(function ($id) use ($products, $inDesignMode) {
                $product = $products->get($id);

                if (! $product) {
                    return null;
                }

                if ($inDesignMode) {
                    ThemeEditor::preloadModel('products', $product);
                }

                return $product;
            })
            ->filter()
            ->values();
    }
}
