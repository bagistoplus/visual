<?php

namespace BagistoPlus\Visual\Sections;

use Webkul\Product\Helpers\ProductType;
use Webkul\Product\Helpers\Review;

class ProductDetails extends BladeSection
{
    public static string $view = 'shop::sections.product-details';

    public static string $schema = __DIR__.'/../../resources/schemas/product-details.json';

    public function getImages()
    {
        return array_map(function ($image) {
            $image['type'] = 'image';

            return $image;
        }, product_image()->getGalleryImages($this->context['product']));
    }

    public function getVideos()
    {
        return product_video()->getVideos($this->context['product']);
    }

    public function getBlocksOnRight()
    {
        return collect($this->section->blocks)->reject(function ($block) {
            return $block->settings->position === 'under_gallery';
        })->all();
    }

    public function getBlocksOnBottom()
    {
        return collect($this->section->blocks)->filter(function ($block) {
            return $block->settings->position === 'under_gallery';
        });
    }

    public function getViewData(): array
    {
        $images = $this->getImages();
        $videos = $this->getVideos();

        $reviewHelper = app(Review::class);

        return [
            'images' => $images,
            'videos' => $videos,
            'medias' => [...$images, ...$videos],

            'totalReviews' => $reviewHelper->getTotalReviews($this->context['product']),
            'averageRating' => $reviewHelper->getAverageRating($this->context['product']),

            'hasVariants' => ProductType::hasVariants($this->context['product']->type),
            'showQuantitySelector' => $this->context['product']->getTypeInstance()->showQuantityBox(),

            'blocksOnRight' => $this->getBlocksOnRight(),
            'blocksOnBottom' => $this->getBlocksOnBottom(),
        ];
    }
}
