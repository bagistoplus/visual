@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.wishlist.page-title')
@stop

@visual_content

@includeIf('shop::templates.account.wishlist')

@end_visual_content
