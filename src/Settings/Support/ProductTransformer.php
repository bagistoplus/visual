<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Webkul\Product\Repositories\ProductRepository;

class ProductTransformer
{
    public function __invoke(?int $id)
    {
        $product = $id ? app(ProductRepository::class)->find($id) : null;

        if (ThemeEditor::inDesignMode() && $product) {
            ThemeEditor::preloadModel('products', $product);
        }

        return $product;
    }
}
