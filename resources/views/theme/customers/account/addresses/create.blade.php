@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.addresses.create.add-address')
@stop

@visual_content

@includeIf('shop::templates.account.add-address')

@end_visual_content
