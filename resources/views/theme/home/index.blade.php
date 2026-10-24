@extends('shop::layouts.default')

@php
  $channel = core()->getCurrentChannel();
@endphp

<!-- SEO Meta Content -->
@push('meta')
  <meta name="title" content="{{ $channel->home_seo['meta_title'] ?? '' }}" />

  <meta name="description" content="{{ $channel->home_seo['meta_description'] ?? '' }}" />

  <meta name="keywords" content="{{ $channel->home_seo['meta_keywords'] ?? '' }}" />
@endPush

@if (filled($channel->home_seo['meta_title'] ?? null))
  @section('page_title')
    {{ $channel->home_seo['meta_title'] }}
  @stop
@endif

@visual_content

@includeIf('shop::templates.index')

@end_visual_content
