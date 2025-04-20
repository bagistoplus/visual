@extends('shop::layouts.default')

@section('page_title')
  {{ trim($product->meta_title) !== '' ? $product->meta_title : $product->name }}
@stop

<!-- SEO Meta Content -->
@push('meta')
  <meta name="description"
    content="{{ trim($product->meta_description) != '' ? $product->meta_description : \Illuminate\Support\Str::limit(strip_tags($product->description), 120, '') }}"
  />

  <meta name="keywords" content="{{ $product->meta_keywords }}" />

  @if (core()->getConfigData('catalog.rich_snippets.products.enable'))
    <script type="application/ld+json">
      {!! app('Webkul\Product\Helpers\SEO')->getProductJsonLd($product) !!}
    </script>
  @endif

  <?php $productBaseImage = product_image()->getProductBaseImage($product); ?>

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="{{ $product->name }}" />
  <meta name="twitter:description" content="{!! htmlspecialchars(trim(strip_tags($product->description))) !!}" />
  <meta name="twitter:image:alt" content="" />
  <meta name="twitter:image" content="{{ $productBaseImage['medium_image_url'] }}" />

  <meta property="og:type" content="og:product" />
  <meta property="og:title" content="{{ $product->name }}" />
  <meta property="og:image" content="{{ $productBaseImage['medium_image_url'] }}" />
  <meta property="og:description" content="{!! htmlspecialchars(trim(strip_tags($product->description))) !!}" />
  <meta property="og:url" content="{{ route('shop.product_or_category.index', $product->url_key) }}" />
@endPush

@visual_content
@include('shop::templates.product')
@end_visual_content
