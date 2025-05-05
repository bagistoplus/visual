<?php

namespace BagistoPlus\Visual\Settings\Support;

use Webkul\Category\Repositories\CategoryRepository;

class CategoryTransformer
{
    public function __invoke(?int $id)
    {
        return $id ? app(CategoryRepository::class)->find($id) : null;
    }
}
