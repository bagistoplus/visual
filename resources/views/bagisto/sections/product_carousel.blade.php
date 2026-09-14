<x-shop::products.carousel
    :title="$data['title'] ?? ''"
    :src="route('shop.api.products.index', $data['filters'] ?? [])"
    :navigation-link="route('shop.search.index', $data['filters'] ?? [])"
    aria-label="{{ trans('shop::app.home.index.product-carousel') }}"
/>
