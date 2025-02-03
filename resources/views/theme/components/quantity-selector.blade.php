@props([
    'label' => __('Quantity'),
    'value' => 1,
    'id' => 'quantity-selector',
])

<div x-data="{
    value: {{ $value }},
    init() {
        Alpine.effect(() => {
            this.$dispatch('change', this.value);
        });
    }
}" {{ $attributes->merge(['class' => 'flex items-center space-x-4']) }}>
  @if ($label)
    <label for="{{ $id }}" class="text-sm font-medium">
      {{ $label }}
    </label>
  @endif
  <div class="focus-within:ring-primary flex items-center rounded-lg border border-gray-300 focus-within:ring-2">
    <button
      type="button"
      aria-label="decrement"
      class="hover:text-primary cursor-pointer p-2 transition-colors"
      x-on:click="value--"
      :disabled="value <= 1"
    >
      <x-lucide-minus class="h-4 w-4" />
      </svg>
    </button>

    <input
      id="{{ $id }}"
      type="text"
      x-model.number="value"
      class="w-16 appearance-none rounded-none border-0 py-1 text-center text-sm focus:ring-0"
    >

    <button
      type="button"
      aria-label="increment"
      class="hover:text-primary p-2 transition-colors"
      x-on:click="value++"
    >
      <x-lucide-plus class="h-4 w-4" />
    </button>
  </div>
</div>
