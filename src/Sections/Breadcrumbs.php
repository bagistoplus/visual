<?php

namespace BagistoPlus\Visual\Sections;

use Diglactic\Breadcrumbs\Breadcrumbs as BreadcrumbsBreadcrumbs;
use Illuminate\Support\Facades\Route;

class Breadcrumbs extends BladeSection
{
    protected static string $view = 'shop::sections.breadcrumbs';

    public function getViewData(): array
    {
        $breadcrumbsData = match (Route::currentRouteName()) {
            'shop.product_or_category.index' => ['name' => 'product', 'entity' => $this->context['product']],
            'shop.checkout.cart.index' => ['name' => 'cart'],
            'shop.checkout.onepage.index' => ['name' => 'checkout'],
            'shop.compare.index' => ['name' => 'compare'],

            // Account pages
            'shop.customers.account.profile.index' => ['name' => 'profile'],
            'shop.customers.account.profile.edit' => ['name' => 'profile.edit'],

            'shop.customers.account.addresses.index' => ['name' => 'addresses'],
            'shop.customers.account.addresses.create' => ['name' => 'addresses.create'],
            'shop.customers.account.addresses.edit' => ['name' => 'addresses.edit', 'entity' => $this->context['address']],

            'shop.customers.account.orders.index' => ['name' => 'orders'],
            'shop.customers.account.orders.view' => ['name' => 'orders.view', 'entity' => $this->context['order']],

            'shop.customers.account.reviews.index' => ['name' => 'reviews'],
            'shop.customers.account.downloadable_products.index' => ['name' => 'downloadable-products'],
            default => []
        };

        $breadcrumbs = empty($breadcrumbsData)
            ? collect([])
            : BreadcrumbsBreadcrumbs::generate(...$breadcrumbsData);

        return ['breadcrumbs' => $breadcrumbs];
    }
}
