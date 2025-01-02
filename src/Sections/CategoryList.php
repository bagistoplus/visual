<?php

namespace BagistoPlus\Visual\Sections;

class CategoryList extends BladeSection
{
    protected static string $view = 'shop::sections.category-list';

    protected static string $schema = __DIR__.'/../../resources/schemas/category-list.json';
}
