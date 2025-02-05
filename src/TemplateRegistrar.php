<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\Template;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\CMS\Repositories\PageRepository;
use Webkul\Product\Repositories\ProductRepository;

class TemplateRegistrar
{
    protected CategoryRepository $categoryRepository;

    protected ProductRepository $productRepository;

    protected PageRepository $pageRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        PageRepository $pageRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Register the theme editor templates.
     */
    public function registerTemplates(): void
    {
        $templates = [
            new Template(
                template: 'index',
                route: 'shop.home.index',
                label: __('visual::theme-editor.templates.homepage'),
                icon: 'lucide-home',
                previewUrl: url()->to('/')
            ),
        ];

        // Add category template if a category with products exists.
        $category = $this->categoryRepository
            ->getModel()
            ->has('products')
            ->first();

        if ($category !== null) {
            $templates[] = new Template(
                template: 'category',
                route: 'shop.categories.index',
                label: __('visual::theme-editor.templates.category'),
                icon: 'lucide-tags',
                previewUrl: $category->url,
            );
        }

        // Add product template if a product exists.
        $product = $this->productRepository->first();

        if ($product !== null && $product->url_key) {
            $templates[] = new Template(
                template: 'product',
                route: 'shop.products.index',
                label: __('visual::theme-editor.templates.product'),
                icon: 'lucide-tag',
                previewUrl: url($product->url_key),
            );
        }

        $templates[] = Template::separator();

        $templates[] = new Template(
            template: 'cart',
            route: 'shop.checkout.cart.index',
            label: __('visual::theme-editor.templates.cart'),
            icon: 'lucide-shopping-cart',
            previewUrl: route('shop.checkout.cart.index')
        );

        $templates[] = new Template(
            template: 'checkout',
            route: 'shop.checkout.onepage.index',
            label: __('visual::theme-editor.templates.checkout'),
            icon: 'lucide-shopping-cart',
            previewUrl: route('shop.checkout.onepage.index')
        );

        $templates[] = new Template(
            template: 'checkout-success',
            route: 'shop.checkout.onepage.success',
            label: __('visual::theme-editor.templates.checkout_success'),
            icon: 'lucide-shopping-cart',
            previewUrl: route('shop.checkout.onepage.success')
        );

        $templates[] = Template::separator();

        $templates[] = new Template(
            template: 'search',
            route: 'shop.search.index',
            label: __('visual::theme-editor.templates.search'),
            icon: 'lucide-search',
            previewUrl: route('shop.search.index')
        );

        // Add CMS page template if any page exists.
        $page = $this->pageRepository->first();

        if ($page !== null) {
            $templates[] = new Template(
                template: 'page',
                route: 'shop.cms.page',
                label: __('visual::theme-editor.templates.cms'),
                icon: 'lucide-file',
                previewUrl: route('shop.cms.page', [$page->url_key])
            );
        }

        $templates[] = new Template(
            template: 'contact',
            route: 'shop.home.contact_us',
            label: __('visual::theme-editor.templates.contact'),
            icon: 'lucide-phone',
            previewUrl: route('shop.home.contact_us')
        );

        foreach ($templates as $template) {
            ThemeEditor::registerTemplate($template);
        }
    }
}
