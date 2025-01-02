<?php

namespace BagistoPlus\Visual\Sections;

use Webkul\Category\Repositories\CategoryRepository;

class Header extends BladeSection
{
    protected static string $view = 'shop::sections.header';

    protected static string $schema = __DIR__.'/../../resources/schemas/header.json';

    public function getCategories()
    {
        // @phpstan-ignore-next-line
        $rootCategoryId = core()->getCurrentChannel()->root_category_id;

        $categories = app(CategoryRepository::class)->getVisibleCategoryTree($rootCategoryId);

        return $categories->filter(fn ($c) => (bool) $c->slug);
    }
}
