@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.gdpr.index.title')
@stop

@visual_content

@includeIf('shop::templates.account.gdpr')

@end_visual_content
