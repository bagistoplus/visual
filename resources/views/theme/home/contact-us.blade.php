@extends('shop::layouts.default')

@section('page_title')
  @lang('shop::app.home.contact.title')
@stop

@visual_content

@includeIf('shop::templates.contact')

@end_visual_content
