@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.downloadable-products.name')
@stop

@visual_content

@includeIf('shop::templates.account.downloadables')

@end_visual_content
