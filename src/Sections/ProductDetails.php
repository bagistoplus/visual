<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Actions\AddProductToCart;
use BagistoPlus\Visual\Enums\Events;
use Webkul\Product\Helpers\ProductType;
use Webkul\Product\Helpers\Review;

class ProductDetails extends LivewireSection
{
    public static string $view = 'shop::sections.product-details';

    public static string $schema = __DIR__.'/../../resources/schemas/product-details.json';

    public int $quantity = 1;

    public array $variantAttributes = [];

    public $selectedVariant;

    public array $groupedProductQuantities = [];

    public array $bundleProductOptions = [];

    public array $bundleProductQuantities = [];

    public array $links = [];

    public function mount(): void
    {
        $this->initializeGroupedProductQuantities();
        $this->initializeBundleProductOptions();
    }

    /**
     * Initialize quantities for grouped products.
     */
    public function initializeGroupedProductQuantities(): void
    {
        $product = $this->context['product'];
        if ($product->type !== 'grouped') {
            return;
        }

        $this->groupedProductQuantities = $product->grouped_products
            ->mapWithKeys(fn ($groupedProduct) => [$groupedProduct->id => $groupedProduct->qty])
            ->all();
    }

    /**
     * Initialize bundle product options and their default quantities.
     */
    public function initializeBundleProductOptions(): void
    {
        $product = $this->context['product'];
        if ($product->type !== 'bundle') {
            return;
        }

        $bundleConfig = app('Webkul\Product\Helpers\BundleOption')->getBundleConfig($product);

        $this->bundleProductOptions = collect($bundleConfig['options'])
            ->mapWithKeys(fn ($bundleOption) => [
                $bundleOption['id'] => collect($bundleOption['products'])
                    ->filter(fn ($p) => $p['is_default'])
                    ->map(fn ($p) => $p['id'])
                    ->all(),
            ])
            ->all();

        $this->bundleProductQuantities = collect($bundleConfig['options'])
            ->filter(fn ($bundleOption) => in_array($bundleOption['type'], ['select', 'radio']))
            ->mapWithKeys(fn ($bundleOption) => [
                $bundleOption['id'] => collect($bundleOption['products'])
                    ->filter(fn ($p) => $p['is_default'])
                    ->map(fn ($p) => $p['qty'])
                    ->first(),
            ])
            ->all();
    }

    /**
     * Add the product to the cart.
     */
    public function addToCart(bool $buyNow = false): void
    {
        $product = $this->context['product'];

        // Build the basic cart parameters
        $cartParams = [
            'product_id' => $product->id,
            'quantity' => $this->quantity,
            'is_buy_now' => $buyNow,
        ];

        // Add configurable options if present
        if (! empty($this->variantAttributes)) {
            $cartParams = array_merge($cartParams, [
                'super_attributes' => $this->variantAttributes,
                'selected_configurable_option' => $this->selectedVariant,
            ]);
        }

        // Add grouped product quantities if present
        if (! empty($this->groupedProductQuantities)) {
            $cartParams = array_merge($cartParams, ['qty' => $this->groupedProductQuantities]);
        }

        // Add bundle product options and quantities if present
        if (! empty($this->bundleProductOptions) && ! empty($this->bundleProductQuantities)) {
            $cartParams = array_merge($cartParams, [
                'bundle_options' => collect($this->bundleProductOptions)->filter()->all(),
                'bundle_option_qty' => $this->bundleProductQuantities,
            ]);
        }

        // Add bundle product options and quantities if present
        if (! empty($this->links)) {
            $cartParams = array_merge($cartParams, ['links' => $this->links]);
        }

        $result = app(AddProductToCart::class)->execute($cartParams);

        if ($result['success']) {
            session()->flash('success', $result['message']);
            $this->dispatch(Events::CART_UPDATED);

            if (! empty($result['redirect_url'])) {
                $this->redirect($result['redirect_url']);
            }
        } else {
            session()->flash('error', $result['message']);
            $this->redirect($result['redirect_url']);
        }
    }

    /**
     * Shortcut to add the product to cart as buy now.
     */
    public function buyNow(): void
    {
        $this->addToCart(buyNow: true);
    }

    /**
     * Get gallery images for the product.
     */
    public function getImages(): array
    {
        $images = product_image()->getGalleryImages($this->context['product']);

        return array_map(fn ($image) => array_merge($image, ['type' => 'image']), $images);
    }

    /**
     * Get videos for the product.
     *
     * @return mixed
     */
    public function getVideos()
    {
        return product_video()->getVideos($this->context['product']);
    }

    /**
     * Get blocks to display on the right side.
     */
    public function getBlocksOnRight(): array
    {
        return collect($this->section->blocks)
            ->reject(fn ($block) => $block->settings->position === 'under_gallery')
            ->all();
    }

    /**
     * Get blocks to display below the gallery.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBlocksOnBottom()
    {
        return collect($this->section->blocks)
            ->filter(fn ($block) => $block->settings->position === 'under_gallery');
    }

    /**
     * Prepare data to pass to the view.
     */
    public function getViewData(): array
    {
        $product = $this->context['product'];
        $images = $this->getImages();
        $videos = $this->getVideos();
        $reviewHelper = app(Review::class);

        return [
            'images' => $images,
            'videos' => $videos,
            'medias' => [...$images, ...$videos],
            'totalReviews' => $reviewHelper->getTotalReviews($product),
            'averageRating' => $reviewHelper->getAverageRating($product),
            'hasVariants' => ProductType::hasVariants($product->type),
            'showQuantitySelector' => $product->getTypeInstance()->showQuantityBox(),
            'blocksOnRight' => $this->getBlocksOnRight(),
            'blocksOnBottom' => $this->getBlocksOnBottom(),
        ];
    }
}
