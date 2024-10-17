<?php

namespace BagistoPlus\Visual\Sections\Concerns;

use Webkul\Product\Repositories\ProductRepository;

class ProductTransformer
{
    public function __invoke(?int $id)
    {
        return $id ? app(ProductRepository::class)->find($id) : null;
    }
}
