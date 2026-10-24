@extends('shop::layouts.account')

@section('page_title')
  @lang('shop::app.customers.account.orders.view.page-title', ['order_id' => $order->increment_id])
@stop

@visual_content

@includeIf('shop::templates.account.order-details')

@end_visual_content
