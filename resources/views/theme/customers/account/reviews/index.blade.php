@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.reviews.title')
@stop

@visual_content

@includeIf('shop::templates.account.reviews')

@end_visual_content
