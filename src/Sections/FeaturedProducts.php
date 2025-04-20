<?php

namespace BagistoPlus\Visual\Sections;

use Webkul\Product\Repositories\ProductFlatRepository;

class FeaturedProducts extends BladeSection
{
    protected static string $view = 'shop::sections.featured-products';

    protected static string $schema = __DIR__.'/../../resources/schemas/featured-products.json';

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
}
