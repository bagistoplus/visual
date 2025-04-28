@php
  $props = [
      'variantAttributes' => $variantAttributes,
      'variantPrices' => $variantPrices,
      'variantImages' => $variantImages,
      'variantVideos' => $variantVideos,
  ];
@endphp

<div x-data x-variant-picker="@js($props)">
  <template x-for="attribute in variantAttributes" x-bind:key="attribute.id">
    <div x-variant-picker:attribute="attribute" class="mb-4 max-w-72">
      <label class="mb-1 block font-medium" x-text="attribute.label">
      </label>

      <template x-if="$attribute.isDropdown">
        <select
          x-bind:id="attribute.id"
          class="py-1"
          x-on:change="$attribute.select(event.target.value)"
        >
          <option x-text="'Select ' + attribute.label"></option>
          <template x-for="option in attribute.options" x-bind:key="option.id">
            <option
              x-bind:selected="selections[attribute.id] == option.id"
              x-bind:value="option.id"
              x-bind:disabled="!option.isAvailable"
              x-text="option.label + (!option.isAvailable ? ' (Unavailable)' : '')"
            ></option>
          </template>
        </select>
      </template>

      <template x-if="attribute.swatch_type === 'color'">
        <div class="flex gap-4">
          <template x-for="option in attribute.options">
            <button
              x-bind:class="[
                  'w-8 h-8 border rounded-full flex items-center justify-center relative',
                  selections[attribute.id] === option.id ? 'ring-2 ring-offset-2 ring-primary' :
                  'hover:ring-2 hover:ring-offset-2 hover:ring-neutral-200',
                  !option.isAvailable ? 'cursor-not-allowed !ring-primary-300' : ''
              ]"
              x-bind:style="{ backgroundColor: option.swatch_value }"
              x-bind:title="option.label + (!option.isAvailable ? ' (Unavailable)' : '')"
              x-on:click="$attribute.select(option.id)"
            >
              <template x-if="!option.isAvailable">
                <div class="absolute inset-0 rounded-full bg-black/20"></div>
              </template>
            </button>
          </template>
        </div>
      </template>

      <template x-if="attribute.swatch_type === 'image'">
        <div class="flex gap-4">
          <template x-for="option in attribute.options">
            <button
              x-bind:class="[
                  'w-10 h-10 rounded-lg relative overflow-hidden',
                  selections[attribute.id] === option.id ? 'ring-2 ring-offset-2 ring-primary' :
                  'hover:ring-2 hover:ring-offset-2 hover:ring-neutral-200',
                  !option.isAvailable ? 'cursor-not-allowed' : ''
              ]"
              x-bind:title="option.label + (!option.isAvailable ? ' (Unavailable)' : '')"
              x-on:click="$attribute.select(option.id)"
            >
              <img x-bind:src="option.swatch_value" class="h-full w-full rounded-lg">
              <template x-if="!option.isAvailable">
                <div class="absolute inset-0 rounded-lg bg-black/30"></div>
              </template>
            </button>
          </template>
        </div>
      </template>

      <template x-if="attribute.swatch_type === 'text'">
        <div class="grid grid-cols-5 gap-2">
          <template x-for="option in attribute.options" :key="option.id">
            <button
              :class="[
                  'py-2 px-3 text-sm font-medium rounded-md relative',
                  selections[attribute.id] === option.id ? 'bg-primary text-primary-50' :
                  'bg-background text-gray-900 border border-neutral-200 hover:bg-neutral-50',
                  !option.isAvailable ? 'cursor-not-allowed text-neutral-300' : ''
              ]"
              :title="option.label + (!option.isAvailable ? ' (Unavailable)' : '')"
              x-on:click="$attribute.select(option.id)"
            >
              <span x-text="option.label"></span>
              <template x-if="!option.isAvailable">
                <div class="absolute inset-0 rounded-md bg-black/30"></div>
              </template>
            </button>
          </template>
        </div>
      </template>
    </div>
  </template>
</div>
