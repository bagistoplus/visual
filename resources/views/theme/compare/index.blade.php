@extends('shop::layouts.default')

@push('meta')
  <meta name="description" content="@lang('shop::app.compare.title')" />
  <meta name="keywords" content="@lang('shop::app.compare.title')" />
@endpush

@section('page_title')
  @lang('shop::app.compare.title')
@stop

@visual_content
@include('shop::templates.compare')
@end_visual_content
