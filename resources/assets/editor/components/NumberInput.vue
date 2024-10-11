<script setup lang="ts">
const value = defineModel({ default: '0'});
const props = defineProps<{
  label: string;
}>();
</script>
<template>
  <ArkNumberInputRoot v-model="value" class="flex flex-col gap-1.5">
    <ArkNumberInputLabel class="text-sm  font-medium">
      {{ props.label }}
    </ArkNumberInputLabel>
    <ArkNumberInputControl :class="[
      'ps-3 h-10 overflow-hidden',
      'text-md',
      'border rounded',
      'grid grid-cols-[1fr_32px] grid-rows-[1fr_1fr]',
      'focus-within:ring focus-within:ring-gray-700'
    ]">
      <ArkNumberInputInput class="border-none bg-transparent row-span-2 outline-none w-full" />
      <ArkNumberInputIncrementTrigger class="text-gray-600 cursor-pointer inline-flex items-center justify-center border-s border-e-0">
        <ChevronUpIcon class="w-4"/>
      </ArkNumberInputIncrementTrigger>
      <ArkNumberInputDecrementTrigger class="text-gray-600 cursor-pointer inline-flex items-center justify-center border-t border-s border-e-0">
        <ChevronDownIcon class="w-4"/>
      </ArkNumberInputDecrementTrigger>
    </ArkNumberInputControl>
  </ArkNumberInputRoot>
</template>
