<script setup lang="ts">
const checked = defineModel<boolean>({ default: false })
const props = defineProps<{
  label?: string;
}>();
</script>

<template>
  <ArkCheckboxRoot v-model:checked="checked" class="gap-2 flex items-center">
    <ArkCheckboxControl :class="[
      'w-4 h-4 rounded-sm border bg-white border-zinc-400 cursor-pointer flex items-center justify-center text-white',
      'data-[state=checked]:bg-gray-700 data-[state=checked]:border-gray-700'
    ]">
      <ArkCheckboxIndicator>
        <CheckIcon class="w-3" />
      </ArkCheckboxIndicator>
    </ArkCheckboxControl>
    <ArkCheckboxLabel v-if="label">{{ props.label }}</ArkCheckboxLabel>
    <ArkCheckboxHiddenInput />
  </ArkCheckboxRoot>
</template>
