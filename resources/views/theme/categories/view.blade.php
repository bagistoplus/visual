@extends('shop::layouts.default')

@push('meta')
  <meta name="description"
    content="{{ trim($category->meta_description) != '' ? $category->meta_description : \Illuminate\Support\Str::limit(strip_tags($category->description), 120, '') }}"
  />

  <meta name="keywords" content="{{ $category->meta_keywords }}" />

  @if (core()->getConfigData('catalog.rich_snippets.categories.enable'))
    <script type="application/ld+json">
      {!! app('Webkul\Product\Helpers\SEO')->getCategoryJsonLd($category) !!}
    </script>
  @endif
@endpush

@section('page_title')
  {{ trim($category->meta_title) != '' ? $category->meta_title : $category->name }}
@stop

@visual_content

@includeIf('shop::templates.category')

@end_visual_content
