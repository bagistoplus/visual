@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.rma.customer.title')
@stop

@visual_content

@includeIf('shop::templates.account.rma')

@end_visual_content
