<?php

namespace BagistoPlus\Visual\Settings\Support;

use Webkul\Product\Repositories\ProductRepository;

class ProductTransformer
{
    public function __invoke(?int $id)
    {
        return $id ? app(ProductRepository::class)->find($id) : null;
    }
}
