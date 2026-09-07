<script setup lang="ts">
import type { PropertyField } from '@craftile/types';

const SCALE: Record<string, string> = {
  none: '0',
  xs: '0.125rem',
  sm: '0.25rem',
  md: '0.375rem',
  lg: '0.5rem',
  xl: '0.75rem',
  full: 'calc(infinity * 1px)',
};

interface Props {
  field: PropertyField & { options?: string[] };
}

const props = defineProps<Props>();

const model = defineModel<string | null>();

const options = computed(() => {
  const requested = props.field.options;

  if (Array.isArray(requested) && requested.length > 0) {
    return requested.filter((key) => key in SCALE);
  }

  return Object.keys(SCALE);
});

const selected = computed(() => {
  if (typeof model.value === 'string' && model.value in SCALE) {
    return model.value;
  }

  if (typeof props.field.default === 'string' && props.field.default in SCALE) {
    return props.field.default;
  }

  return 'md';
});

function select(key: string) {
  model.value = key;
}
</script>

<template>
  <div class="flex flex-col gap-1">
    <label
      v-if="field.label"
      class="text-sm font-medium text-gray-700"
    >
      {{ field.label }}
    </label>

    <div class="grid grid-cols-7 gap-1">
      <button
        v-for="key in options"
        :key="key"
        type="button"
        :title="key"
        :aria-pressed="key === selected"
        class="flex aspect-square items-center justify-center rounded border-2 bg-gray-100 transition-colors hover:border-gray-300"
        :class="key === selected ? 'border-gray-500' : 'border-gray-100'"
        @click="select(key)"
      >
        <span
          class="block w-5 h-5 border-2 border-gray-400"
          :style="{ borderRadius: SCALE[key] }"
        ></span>
      </button>
    </div>
  </div>
</template>
