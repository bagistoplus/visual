<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Sections\Settings\Checkbox;
use BagistoPlus\Visual\Sections\Settings\Image;
use BagistoPlus\Visual\Sections\Settings\Text;
use Webkul\Category\Repositories\CategoryRepository;

class Header extends BladeSection
{
    protected static string $view = 'shop::sections.header';

    public static function blocks(): array
    {
        return [
            Block::make('logo', __('visual::sections.header.blocks.logo.name'))
                ->limit(1)
                ->settings([
                    Image::make('logo', __('visual::sections.header.blocks.logo.settings.logo_image_label')),

                    Text::make('logo_text', __('visual::sections.header.blocks.logo.settings.logo_text_label')),
                ]),

            Block::make('nav', __('visual::sections.header.blocks.nav.name'))
                ->limit(1)
                ->settings([
                    Checkbox::make('push_to_left', 'Push to left')
                        ->default(true),

                    Checkbox::make('push_to_right', 'Push to right')
                        ->default(false),
                ]),

            Block::make('currency', __('visual::sections.header.blocks.currency.name'))->limit(1),
            Block::make('locale', __('visual::sections.header.blocks.locale.name'))->limit(1),
            Block::make('search', __('visual::sections.header.blocks.search.name'))->limit(1),
            Block::make('compare', __('visual::sections.header.blocks.compare.name'))->limit(1),
            Block::make('user', __('visual::sections.header.blocks.user.name'))->limit(1),
            Block::make('cart', __('visual::sections.header.blocks.cart.name'))->limit(1),
        ];
    }

    public static function default(): array
    {
        return [
            'blocks' => [
                ['type' => 'logo'],
                ['type' => 'nav'],
                ['type' => 'currency'],
                ['type' => 'locale'],
                ['type' => 'search'],
                ['type' => 'compare'],
                ['type' => 'user'],
                ['type' => 'cart'],
            ],
        ];
    }

    public function getCategories()
    {
        // @phpstan-ignore-next-line
        $rootCategoryId = core()->getCurrentChannel()->root_category_id;

        $categories = app(CategoryRepository::class)->getVisibleCategoryTree($rootCategoryId);

        return $categories->filter(fn ($c) => (bool) $c->slug);
    }
}
