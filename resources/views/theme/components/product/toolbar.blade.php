@props(['availableSortOptions' => collect(), 'availablePaginationLimits' => collect()])

<div {{ $attributes->merge(['class' => 'mb-6 hidden flex-wrap justify-between gap-4 md:flex']) }}>
  <div class="flex items-center gap-4">
    @if ($availableSortOptions->isNotEmpty())
      <select
        class="w-auto"
        name="sort"
        aria-label="Sort by"
        wire:model.live="sort"
      >
        @foreach ($availableSortOptions as $option)
          <option value="{{ $option['value'] }}">
            {{ $option['title'] }}
          </option>
        @endforeach
      </select>
    @endif

    @if ($availablePaginationLimits->isNotEmpty())
      <select
        class="w-auto"
        name="limit"
        aria-label="Items per page"
        wire:model.live="limit"
      >
        @foreach ($availablePaginationLimits as $limit)
          <option value="{{ $limit }}">
            {{ $limit }}
          </option>
        @endforeach
      </select>
    @endif
  </div>

  <div class="flex gap-2">
    <button
      aria-label="Show as grid"
      x-on:click="displayMode = 'grid'"
      class="focus:ring-primary inline-flex h-10 w-10 items-center justify-center rounded-lg focus:ring-2 focus:ring-offset-2"
      x-bind:class="{ 'bg-primary text-primary-100': displayMode === 'grid', 'bg-surface-alt-600 text-neutral-600': displayMode !== 'grid' }"
    >
      <x-lucide-layout-grid class="h-5 w-5" />
    </button>

    <button
      aria-label="Show as list"
      x-on:click="displayMode ='list'"
      class="focus:ring-primary inline-flex h-10 w-10 items-center justify-center rounded-lg focus:ring-2 focus:ring-offset-2"
      x-bind:class="{ 'bg-primary text-primary-100': displayMode === 'list', 'bg-surface-alt-600 text-neutral-600': displayMode !== 'list' }"
    >
      <x-lucide-layout-list class="h-5 w-5" />
    </button>
  </div>
</div>
