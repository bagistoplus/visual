@extends('shop::layouts.default')

@section('page_title')
  @lang('visual::shop.eu-withdrawal.lookup.page-title')
@stop

@visual_content

@includeIf('shop::templates.withdrawal.lookup')

@end_visual_content
