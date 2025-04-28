<div
  x-accordion:item-content
  x-collapse
  {{ $attributes->merge(['class' => 'pb-2']) }}
>
  {{ $slot }}
</div>
