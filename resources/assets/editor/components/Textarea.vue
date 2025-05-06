<script setup lang="ts">
  import { Field } from '@ark-ui/vue/field';

  const value = defineModel<string>()
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

    <Field.Textarea
      v-model="value"
      :placeholder="placeholder"
      :class="[
        'px-2 py-2 h-20 text-surface-500 appearance-none relative',
        'rounded border border-gray-300 outline-0',
        'focus:ring focus:ring-gray-700'
      ]"
    />
  </Field.Root>
</template>