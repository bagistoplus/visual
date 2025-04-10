@php
  $alpineProps = [
      'productId' => $productId,
      'userLoggedIn' => auth('customer')->check(),
      'messages' => [
          'alreadyInCompare' => trans('shop::app.products.view.already-in-compare'),
          'addedToCompare' => trans('shop::app.products.view.add-to-compare'),
      ],
  ];
@endphp

<x-shop::ui.button
  x-data="VisualAddToCompare({{ Js::from($alpineProps) }})"
  x-on:click="handle"
  wire:loading.attr="disabled"
  title="{{ trans('shop::app.components.products.card.add-to-compare') }}"
  variant="soft"
  color="secondary"
  icon="lucide-arrow-left-right"
  icon-only
  rounded
  {{ $attributes }}
/>
