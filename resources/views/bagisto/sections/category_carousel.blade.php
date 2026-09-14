<x-shop::categories.carousel
    :title="$data['title'] ?? ''"
    :src="route('shop.api.categories.index', $data['filters'] ?? [])"
    :navigation-link="route('shop.home.index')"
    aria-label="{{ trans('shop::app.home.index.categories-carousel') }}"
/>
