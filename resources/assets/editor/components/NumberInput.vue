<script setup lang="ts">
  import { NumberInput } from '@ark-ui/vue/number-input';

  const value = defineModel({ default: '0' });
  const props = defineProps<{
    label?: string;
  }>();
</script>

<template>
  <NumberInput.Root
    v-model="value"
    class="flex flex-col gap-1.5"
  >
    <NumberInput.Label
      v-if="label"
      class="text-sm  font-medium"
    >
      {{ props.label }}
    </NumberInput.Label>

    <NumberInput.Control :class="[
      'ps-3 h-10 overflow-hidden',
      'text-md',
      'border rounded',
      'grid grid-cols-[1fr_32px] grid-rows-[1fr_1fr]',
      'focus-within:ring focus-within:ring-gray-700'
    ]">
      <NumberInput.Input class="border-none bg-transparent row-span-2 outline-none w-full" />
      <NumberInput.IncrementTrigger class="text-gray-600 cursor-pointer inline-flex items-center justify-center border-s border-e-0">
        <i-heroicons-chevron-up class="w-4" />
      </NumberInput.IncrementTrigger>
      <NumberInput.DecrementTrigger class="text-gray-600 cursor-pointer inline-flex items-center justify-center border-t border-s border-e-0">
        <i-heroicons-chevron-down class="w-4" />
      </NumberInput.DecrementTrigger>
    </NumberInput.Control>
  </NumberInput.Root>
</template>