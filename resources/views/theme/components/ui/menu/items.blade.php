<div
  x-cloak
  {{ $attributes->merge(['class' => 'absolute bg-surface rounded-lg py-2 shadow-lg min-w-48 w-max data-[placement=end]:right-0 data-[placement=start]:left-0']) }}
  x-dropdown:content
>
  {{ $slot }}
</div>
