<x-shop::carousel
    :options="$section->getTypeInstance()?->sanitize((array) $data) ?? $data"
    aria-label="{{ trans('shop::app.home.index.image-carousel') }}"
/>
