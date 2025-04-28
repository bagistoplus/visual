@extends('shop::layouts.default')

<!-- SEO Meta Content -->
@push('meta')
  <meta name="description" content="@lang('shop::app.customers.forgot-password.title')" />
  <meta name="keywords" content="@lang('shop::app.customers.forgot-password.title')" />
@endPush

@section('page_title')
  @lang('shop::app.customers.forgot-password.title')
@endsection

@visual_content

@include('shop::templates.auth.forgot-password')

@end_visual_content
