@extends('shop::layouts.default')

<!-- SEO Meta Content -->
@push('meta')
  <meta name="description" content="@lang('shop::app.checkout.onepage.index.checkout')" />
  <meta name="keywords" content="@lang('shop::app.checkout.onepage.index.checkout')" />
@endPush

@section('page_title')
  @lang('shop::app.checkout.onepage.index.checkout')
@endsection

@visual_content

@includeIf('shop::templates.checkout')

@end_visual_content
