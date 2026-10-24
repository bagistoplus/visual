@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.profile.index.title')
@stop

@visual_content

@includeIf('shop::templates.account.profile')

@end_visual_content
