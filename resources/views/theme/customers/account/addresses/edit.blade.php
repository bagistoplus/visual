@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.addresses.edit.edit')
@stop

@visual_content

@includeIf('shop::templates.account.edit-address')

@end_visual_content
