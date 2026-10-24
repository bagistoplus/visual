@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.rma.customer.create.view')
@stop

@visual_content

@includeIf('shop::templates.account.rma-details')

@end_visual_content
