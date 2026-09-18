<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\ResolvedModelCache;
use Webkul\Product\Repositories\ProductRepository;

class ProductTransformer
{
    public function __invoke(?int $id, array $schema = [])
    {
        if (! $id) {
            return null;
        }

        $product = app(ResolvedModelCache::class)->rememberProduct(
            $id,
            fn () => app(ProductRepository::class)->find($id)
        );

        if (ThemeEditor::inDesignMode() && $product) {
            ThemeEditor::preloadModel('products', $product);
        }

        return $product;
    }
}
