@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.orders.title')
@stop

@visual_content

@includeIf('shop::templates.account.orders')

@end_visual_content
