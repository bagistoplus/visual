<x-shop::ui.button
  wire:loading.attr="disabled"
  wire:click="handle"
  title="{{ trans('shop::app.components.products.card.add-to-compare') }}"
  variant="soft"
  color="secondary"
  icon="lucide-arrow-left-right"
  icon-only
  rounded
  {{ $attributes }}
/>
