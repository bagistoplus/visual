<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Sections\Settings\Product;
use BagistoPlus\Visual\Sections\Settings\Range;
use BagistoPlus\Visual\Sections\Settings\Select;
use BagistoPlus\Visual\Sections\Settings\Text;
use Webkul\Product\Repositories\ProductFlatRepository;

class FeaturedProducts extends BladeSection
{
    protected static array $disabledOn = ['auth/*', 'account/*'];

    protected static string $view = 'shop::sections.featured-products';

    public function getProducts()
    {
        if (count($this->section->blocks) > 0) {
            return collect($this->section->blocks)
                ->map(function ($block) {
                    return $block->settings->product;
                })
                ->filter();
        }

        if ($this->section->settings->product_type === 'featured') {
            return $this->getFeaturedProducts();
        }

        return $this->getNewProducts();
    }

    protected function getFeaturedProducts($count = 4)
    {
        return app(ProductFlatRepository::class)
            ->with(['product'])
            ->scopeQuery(function ($query) {
                $channel = app('core')->getRequestedChannelCode();
                $locale = app('core')->getRequestedLocaleCode();

                return $query->distinct()
                    ->addSelect('product_flat.*')
                    ->where('product_flat.status', 1)
                    ->where('product_flat.visible_individually', 1)
                    ->where('product_flat.featured', 1)
                    ->where('product_flat.channel', $channel)
                    ->where('product_flat.locale', $locale)
                    ->inRandomOrder();
            })
            ->take($this->section->settings->nb_products)
            ->get()
            ->map->product;
    }

    protected function getNewProducts()
    {
        return app(ProductFlatRepository::class)
            ->with(['product'])
            ->scopeQuery(function ($query) {
                $channel = app('core')->getRequestedChannelCode();
                $locale = app('core')->getRequestedLocaleCode();

                return $query->distinct()
                    ->addSelect('product_flat.*')
                    ->where('product_flat.status', 1)
                    ->where('product_flat.visible_individually', 1)
                    ->where('product_flat.new', 1)
                    ->where('product_flat.channel', $channel)
                    ->where('product_flat.locale', $locale)
                    ->inRandomOrder();
            })
            ->take($this->section->settings->nb_products)
            ->get()
            ->map->product;
    }

    public static function settings(): array
    {
        return [
            Text::make('heading', __('visual::sections.featured-products.settings.heading_label'))
                ->default(__('visual::sections.featured-products.settings.heading_default')),

            Text::make('subheading', __('visual::sections.featured-products.settings.subheading_label'))
                ->default(__('visual::sections.featured-products.settings.subheading_default')),

            Range::make('nb_products', __('visual::sections.featured-products.settings.nb_products_label'))
                ->default(4)
                ->min(1)
                ->max(4)
                ->step(1)
                ->info(__('visual::sections.featured-products.settings.nb_products_info')),

            Select::make('product_type', __('visual::sections.featured-products.settings.product_type_label'))
                ->options([
                    'new' => 'New',
                    'featured' => 'Featured',
                ])
                ->default('new')
                ->info(__('visual::sections.featured-products.settings.product_type_info')),
        ];
    }

    public static function blocks(): array
    {
        return [
            Block::make('product', __('visual::sections.featured-products.blocks.product.name'))
                ->limit(4)
                ->settings([
                    Product::make('product', __('visual::sections.featured-products.blocks.product.settings.product_label'))
                        ->info(__('visual::sections.featured-products.blocks.product.settings.product_info')),
                ]),
        ];
    }
}
