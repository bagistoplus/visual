@props(['product'])

<div
  x-data="VisualProductPrices"
  x-bind="bindings"
  class="text-primary [&>div>p:nth-of-type(2)]:text-neutral flex items-center gap-2 text-lg font-medium [&>div>p:nth-of-type(2)]:text-xs [&>div]:flex [&>div]:items-center [&_.line-through]:text-neutral-400"
>
  {!! $product->getTypeInstance()->getPriceHtml() !!}
</div>
