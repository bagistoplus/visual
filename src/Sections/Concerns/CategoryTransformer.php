<?php

namespace BagistoPlus\Visual\Sections\Concerns;

use Webkul\Category\Repositories\CategoryRepository;

class CategoryTransformer
{
    public function __invoke(?int $id)
    {
        return $id ? app(CategoryRepository::class)->find($id) : null;
    }
}
