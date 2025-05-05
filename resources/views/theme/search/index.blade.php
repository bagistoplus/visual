@extends('shop::layouts.default')

@php
  $title = request()->has('query') ? trans('shop::app.search.title', ['query' => request()->query('query')]) : trans('shop::app.search.results');
@endphp

@push('meta')
  <meta name="description" content="{{ $title }}" />

  <meta name="keywords" content="{{ $title }}" />
@endpush

@section('page_title')
  {{ $title }}
@stop

@visual_content

@includeIf('shop::templates.search')

@end_visual_content
