<?php

namespace BagistoPlus\Visual\Sections;

use Diglactic\Breadcrumbs\Breadcrumbs as BreadcrumbsBreadcrumbs;
use Illuminate\Support\Facades\Route;

class Breadcrumbs extends BladeSection
{
    public static string $view = 'shop::sections.breadcrumbs';

    public function getViewData(): array
    {
        $breadcrumbsData = match (Route::currentRouteName()) {
            'shop.product_or_category.index' => ['name' => 'product', 'entity' => $this->context['product']],
            'shop.checkout.cart.index' => ['name' => 'cart'],
            'shop.checkout.onepage.index' => ['name' => 'checkout'],
            'shop.compare.index' => ['name' => 'compare'],

            // Account pages
            'shop.customers.account.profile.index' => ['name' => 'profile'],
            default => []
        };

        $breadcrumbs = empty($breadcrumbsData)
            ? collect([])
            : BreadcrumbsBreadcrumbs::generate(...$breadcrumbsData);

        return ['breadcrumbs' => $breadcrumbs];
    }
}
