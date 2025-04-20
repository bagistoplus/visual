@props(['maxPrice', 'noHeader' => false])

<div {{ $attributes->merge(['class' => 'space-y-6']) }}>
  @if (!$noHeader)
    <div class="flex items-center justify-between">
      <h3 class="font-medium text-neutral-700">
        @lang('shop::app.categories.filters.filters')
      </h3>
      <button wire:click="resetFilters" class="text-primary hover:text-primary/80 flex items-center text-sm">
        <x-lucide-rotate-ccw class="mr-1 h-4 w-4" />
        @lang('visual::shop.reset')
      </button>
    </div>
  @endif

  <x-shop::ui.accordion :value="$this->availableFilters->pluck('id')" multiple>
    @foreach ($this->availableFilters as $filter)
      <x-shop::ui.accordion.item :id="$filter->id" :title="$filter->name">
        @if ($filter->type === 'price')
          <div class="py-3 pr-3">
            <x-shop::range-input
              :gap="16"
              :max="intval($maxPrice)"
              :value="[0, intval($maxPrice)]"
              x-on:change.debounce="$wire.setFilter('{{ $filter->code }}', $event.detail)"
            />
          </div>
        @else
          <div class="space-y-2 py-3">
            @foreach ($filter->options as $option)
              <label class="flex items-center">
                <input
                  wire:model.live="filters.{{ $filter->code }}"
                  type="checkbox"
                  name="{{ $filter->code }}"
                  value="{{ $option->id }}"
                >
                <span class="ml-3">{{ $option->label ?? $option->admin_name }}</span>
              </label>
            @endforeach
          </div>
        @endif
      </x-shop::ui.accordion.item>
    @endforeach
  </x-shop::ui.accordion>
</div>
