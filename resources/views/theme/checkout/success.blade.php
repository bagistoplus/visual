@extends('shop::layouts.default')

@section('page_title')
  @lang('shop::app.checkout.success.thanks')
@endsection

@visual_content

@includeIf('shop::templates.checkout-success')

@end_visual_content
