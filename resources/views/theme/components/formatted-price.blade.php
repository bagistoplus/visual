@props(['price'])

{{ app('core')->formatPrice($price) }}
