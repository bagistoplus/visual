<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Actions\AddProductToCart;
use Webkul\Product\Helpers\ProductType;
use Webkul\Product\Helpers\Review;

class ProductDetails extends LivewireSection
{
    public static string $view = 'shop::sections.product-details';

    public static string $schema = __DIR__.'/../../resources/schemas/product-details.json';

    public $quantity = 1;

    public $variantAttributes = [];

    public $selectedVariant;

    public $groupedProductQuantities = [];

    public function mount()
    {
        $this->initializeGroupedProductQuantities();
    }

    public function initializeGroupedProductQuantities()
    {
        if ($this->context['product']->type !== 'grouped') {
            return;
        }

        $this->groupedProductQuantities = $this->context['product']->grouped_products
            ->mapWithKeys(function ($groupedProduct) {
                return [$groupedProduct->id => $groupedProduct->qty];
            })->all();
    }

    public function addToCart(bool $buyNow = false)
    {
        $result = app(AddProductToCart::class)->execute(array_merge(
            [
                'product_id' => $this->context['product']->id,
                'quantity' => $this->quantity,
                'is_buy_now' => $buyNow,
            ],
            empty($this->variantAttributes) ? [] : ['super_attributes' => $this->variantAttributes, 'selected_configurable_option' => $this->selectedVariant],
            empty($this->groupedProductQuantities) ? [] : ['qty' => $this->groupedProductQuantities],
        ));

        if ($result['success']) {
            session()->flash('success', $result['message']);
            $this->dispatch('cartUpdated');

            if ($result['redirect_url']) {
                $this->redirect($result['redirect_url']);
            }
        } else {
            session()->flash('error', $result['message']);
            $this->redirect($result['redirect_url']);
        }
    }

    public function buyNow()
    {
        return $this->addToCart(buyNow: true);
    }

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
