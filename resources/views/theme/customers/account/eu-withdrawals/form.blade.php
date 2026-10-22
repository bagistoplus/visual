@extends('shop::layouts.account')

@section('page_title')
  @lang('visual::shop.eu-withdrawal.form.page-title')
@stop

@visual_content

@includeIf('shop::templates.account.withdrawal-form')

@end_visual_content
