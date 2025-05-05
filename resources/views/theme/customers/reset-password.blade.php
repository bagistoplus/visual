@extends('shop::layouts.default')

<!-- SEO Meta Content -->
@push('meta')
  <meta name="description" content="@lang('shop::app.customers.reset-password.title')" />
  <meta name="keywords" content="@lang('shop::app.customers.reset-password.title')" />
@endPush

@section('page_title')
  @lang('shop::app.customers.reset-password.title')
@endsection

@visual_content

@includeIf('shop::templates.auth.reset-password')

@end_visual_content
