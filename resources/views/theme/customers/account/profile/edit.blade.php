@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.profile.edit.edit-profile')
@stop

@visual_content

@includeIf('shop::templates.account.edit-profile')

@end_visual_content
