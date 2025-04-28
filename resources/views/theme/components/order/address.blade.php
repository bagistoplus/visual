@props(['address'])

@if ($address->compamy_name)
  {{ $address->compamy_name }}<br />
@endif
{{ $address->name }}<br />
{{ $address->address }}<br />
{{ $address->city }}<br />
{{ $address->state }}<br />
{{ core()->country_name($address->country) }} @if ($address->postcode)
  ({{ $address->postcode }})
@endif
<br>
{{ __('shop::app.customers.account.orders.view.contact') }} : {{ $address->phone }}
