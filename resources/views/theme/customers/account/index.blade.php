@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.layouts.my-account')
@stop

@visual_content

@includeIf('shop::templates.account.index')

@end_visual_content
