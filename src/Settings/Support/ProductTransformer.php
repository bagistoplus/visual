<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\ResolvedModelCache;
use Webkul\Product\Repositories\ProductRepository;

class ProductTransformer
{
    /**
     * What a rendered product reads. Both product transformers load these, so a
     * product remembered by either is complete whichever one asks for it next.
     *
     * @var list<string>
     */
    public const RELATIONS = [
        'attribute_family',
        'images',
        'attribute_values',
        'price_indices',
        'inventory_indices',
        'reviews',
    ];

    public function __invoke(?int $id, array $schema = [])
    {
        if (! $id) {
            return null;
        }

        $product = app(ResolvedModelCache::class)->rememberProduct(
            $id,
            fn () => app(ProductRepository::class)->with(self::RELATIONS)->find($id)
        );

        if (ThemeEditor::inDesignMode() && $product) {
            ThemeEditor::preloadModel('products', $product);
        }

        return $product;
    }
}
