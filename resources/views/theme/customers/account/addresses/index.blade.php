@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.addresses.index.title')
@stop

@visual_content

@includeIf('shop::templates.account.addresses')

@end_visual_content
