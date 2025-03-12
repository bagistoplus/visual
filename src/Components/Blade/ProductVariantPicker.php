<?php

namespace BagistoPlus\Visual\Components\Blade;

use Illuminate\View\Component;
use Webkul\Product\Helpers\ConfigurableOption;

class ProductVariantPicker extends Component
{
    public $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function isDropdownSwatch($swatchType)
    {
        return ! $swatchType || $swatchType === 'dropdown';
    }

    public function render()
    {
        $variantHelper = app(ConfigurableOption::class);
        $variantConfig = $variantHelper->getConfigurationConfig($this->product);

        return view('shop::components.products.variant-picker', [
            'variantAttributes' => $variantConfig['attributes'],
            'variantPrices' => $variantConfig['variant_prices'],
            'variantImages' => $variantConfig['variant_images'],
            'variantVideos' => $variantConfig['variant_videos'],
        ]);
    }
}
