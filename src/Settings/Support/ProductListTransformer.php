<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use Illuminate\Support\Collection;
use Webkul\Product\Repositories\ProductRepository;

class ProductListTransformer implements SettingTransformerInterface
{
    /**
     * `$product->name` alone is an EAV lookup, so without these a card grid
     * issues several queries per product.
     *
     * @var list<string>
     */
    private const RELATIONS = [
        'attribute_family',
        'images',
        'attribute_values',
        'price_indices',
        'inventory_indices',
        'reviews',
    ];

    public function transform(mixed $value, array $schema = []): mixed
    {
        if (! is_array($value) || empty($value)) {
            return collect();
        }

        $products = app(ProductRepository::class)
            ->with(self::RELATIONS)
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
