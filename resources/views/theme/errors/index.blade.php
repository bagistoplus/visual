@extends('shop::layouts.default')

@section('page_title')
  @lang("admin::app.errors.{$errorCode}.title")
@stop

@visual_content

@includeIf('shop::templates.error')

@end_visual_content
