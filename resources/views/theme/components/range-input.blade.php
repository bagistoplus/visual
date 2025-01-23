@props([
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'gap' => 0,
])

<div x-data="rangeInput" {{ $attributes }}>
  <div class="relative h-1 rounded-full bg-neutral-200">
    <div class="bg-primary absolute inset-y-0 rounded-md"
      x-bind:style="'right:' + maxthumb + '%; left:' + minthumb + '%'"></div>

    <input aria-label="Min range" type="range" step="{{ $step }}" x-bind:min="min"
      x-bind:max="max" x-on:input="handleMin" x-model="minValue" class="absolute w-full">

    <input aria-label="Max range" type="range" step="{{ $step }}" x-bind:min="min"
      x-bind:max="max" x-on:input="handleMax" x-model="maxValue" class="absolute w-full">
  </div>

  <div class="flex items-center justify-between py-5">
    <div class="relative">
      <span class="absolute left-2 top-1/2 -translate-y-1/2 transform">{{ core()->getCurrentCurrency()->symbol }}</span>
      <input type="text" aria-label="Min value" maxlength="5" x-on:input="handleMin" x-model="minValue"
        class="w-24 rounded border border-gray-200 py-1 pl-6 pr-2 text-right text-sm">
    </div>

    <div class="relative">
      <span class="absolute left-2 top-1/2 -translate-y-1/2 transform">{{ core()->getCurrentCurrency()->symbol }}</span>
      <input type="text" aria-label="Max value" maxlength="5" x-on:input="handleMax" x-model="maxValue"
        class="w-24 rounded border border-gray-200 py-1 pl-6 pr-2 text-right text-sm">
    </div>
  </div>
</div>

@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', function() {
      Alpine.data('rangeInput', function() {
        return {
          minValue: {{ $min }},
          maxValue: {{ $max }},
          min: {{ $min }},
          max: {{ $max }},
          gap: {{ $gap }},
          minthumb: 0,
          maxthumb: 0,

          init() {
            if (this.gap <= 0) {
              this.gap = this.max * 0.1;
            }

            this.handleMin();
            this.handleMax();
          },

          handleMin() {
            this.minValue = Math.min(this.minValue, this.maxValue - this.gap);
            this.minthumb = ((this.minValue - this.min) / (this.max - this.min)) * 100;

            this.emitChange();
          },

          handleMax() {
            this.maxValue = Math.max(this.maxValue, this.minValue + this.gap);
            this.maxthumb = 100 - (((this.maxValue - this.min) / (this.max - this.min)) * 100);

            this.emitChange();
          },

          emitChange() {
            if (this.minValue !== this.min || this.maxValue !== this.max) {
              this.$dispatch('range-change', [this.minValue, this.maxValue]);
            }
          }
        }
      });
    })
  </script>
@endPushOnce
