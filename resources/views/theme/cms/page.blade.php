@extends('shop::layouts.default')

<!-- SEO Meta Content -->
@push('meta')
  <meta name="title" content="{{ $page->meta_title }}" />
  <meta name="description" content="{{ $page->meta_description }}" />
  <meta name="keywords" content="{{ $page->meta_keywords }}" />
@endpush

@section('page_title')
  {{ $page->meta_title }}
@stop

@visual_content
@include('shop::templates.page')
@end_visual_content
