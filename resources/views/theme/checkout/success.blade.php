@extends('shop::layouts.default')

@section('page_title')
  @lang('shop::app.checkout.success.thanks')
@endsection

@visual_content

@include('shop::templates.checkout-success')

@end_visual_content
