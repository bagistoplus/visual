<script setup lang="ts">
  import { Field } from '@ark-ui/vue/field';

  const value = defineModel<string>();
  const props = defineProps<{
    label?: string;
    placeholder?: string;
  }>();
</script>

<template>
  <Field.Root class="flex flex-col gap-1.5">
    <Field.Label
      v-if="label"
      class="text-sm font-medium"
    >
      {{ props.label }}
    </Field.Label>
    <Field.Input
      v-model="value"
      :placeholder="placeholder"
      :class="[
        'px-3 h-10 text-surface-500 ',
        'rounded border border-gray-300',
        'focus:outline-none focus:ring focus:ring-gray-700'
      ]"
    />
  </Field.Root>
</template>