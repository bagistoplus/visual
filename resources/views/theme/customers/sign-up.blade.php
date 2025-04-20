@extends('shop::layouts.default')

<!-- SEO Meta Content -->
@push('meta')
  <meta name="description" content="@lang('shop::app.customers.signup-form.page-title')" />
  <meta name="keywords" content="@lang('shop::app.customers.signup-form.page-title')" />
@endPush

@section('page_title')
  @lang('shop::app.customers.signup-form.page-title')
@endsection

@visual_content

@include('shop::templates.auth.register')

@end_visual_content
