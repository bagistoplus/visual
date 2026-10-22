@extends('shop::layouts.default')

@section('page_title')
  @lang('visual::shop.eu-withdrawal.confirmation.page-title')
@stop

@visual_content

@includeIf('shop::templates.withdrawal.confirmation')

@end_visual_content
