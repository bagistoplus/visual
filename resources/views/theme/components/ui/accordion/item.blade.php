@props(['id' => null, 'title' => null])

@php
  $itemAttr = $id ? ['x-accordion:item' => Js::from($id)] : ['x-accordion:item' => ''];
@endphp

<div {{ $attributes->merge(['class' => 'border-b last:border-none'])->merge($itemAttr) }}>
  @if ($title)
    <h3>
      <x-shop::ui.accordion.item-trigger class="w-full">
        {{ $title }}
      </x-shop::ui.accordion.item-trigger>
    </h3>
    <x-shop::ui.accordion.item-content>
      {{ $slot }}
    </x-shop::ui.accordion.item-content>
  @else
    {{ $slot }}
  @endif
</div>
