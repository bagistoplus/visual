@extends('shop::layouts.default')

<!-- SEO Meta Content -->
@push('meta')
  <meta name="description" content="@lang('shop::app.customers.login-form.page-title')" />
  <meta name="keywords" content="@lang('shop::app.customers.login-form.page-title')" />
@endPush

@section('page_title')
  @lang('shop::app.customers.login-form.page-title')
@endsection

@visual_content

@includeIf('shop::templates.auth.login')

@end_visual_content
