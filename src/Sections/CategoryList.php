<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Sections\Settings\Category;
use BagistoPlus\Visual\Sections\Settings\Range;
use BagistoPlus\Visual\Sections\Settings\Select;
use BagistoPlus\Visual\Sections\Settings\Text;

class CategoryList extends BladeSection
{
    protected static string $view = 'shop::sections.category-list';

    public static function settings(): array
    {
        return [
            Text::make('heading', __('visual::sections.category-list.settings.heading_label'))
                ->default(__('visual::sections.category-list.settings.heading_default')),

            Select::make('heading_size', __('visual::sections.category-list.settings.heading_size_label'))
                ->options([
                    ['value' => 'small', 'label' => 'Small'],
                    ['value' => 'medium', 'label' => 'Medium'],
                    ['value' => 'large', 'label' => 'Large'],
                ])
                ->default('medium'),

            Range::make('columns_desktop', __('visual::sections.category-list.settings.columns_desktop_label'))
                ->min(1)
                ->max(6)
                ->default(2),

            Select::make('columns_mobile', __('visual::sections.category-list.settings.columns_mobile_label'))
                ->options([
                    1 => '1',
                    2 => '2',
                ])
                ->default(1),
        ];
    }

    public static function blocks(): array
    {
        return [
            Block::make('category', __('visual::sections.category-list.blocks.category.name'))
                ->limit(15)
                ->settings([
                    Category::make('category', __('visual::sections.category-list.blocks.category.settings.category_label'))
                        ->default(null),
                ]),
        ];
    }
}
