@extends('shop::layouts.account')

@section('page_title')
  @lang('visual::shop.eu-withdrawal.confirmation.page-title')
@stop

@visual_content

@includeIf('shop::templates.account.withdrawal')

@end_visual_content
