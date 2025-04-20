<button x-accordion:item-trigger {{ $attributes->merge(['class' => 'flex items-center py-2 w-full']) }}>
  <span>{{ $slot }}</span>
  <span x-accordion:item-indicator class="ml-auto transition-transform duration-200 data-[state=open]:rotate-180">
    <x-lucide-chevron-down class="h-4 w-4" />
  </span>
</button>
