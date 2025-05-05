@extends('shop::layouts.default')

<!-- SEO Meta Content -->
@push('meta')
  <meta name="description" content="@lang('shop::app.checkout.cart.index.cart')" />
  <meta name="keywords" content="@lang('shop::app.checkout.cart.index.cart')" />
@endPush

@section('page_title')
  @lang('shop::app.checkout.cart.index.cart')
@endsection

@visual_content

@includeIf('shop::templates.cart')

@end_visual_content
