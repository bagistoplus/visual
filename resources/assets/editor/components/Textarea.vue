<script setup lang="ts">
const value = defineModel<string>()
const props = defineProps<{
  label: string;
}>();
</script>

<template>
  <ArkFieldRoot class="flex flex-col gap-1.5">
    <ArkFieldLabel class="text-sm font-medium">
      {{ props.label }}
    </ArkFieldLabel>
    <ArkFieldTextarea v-model="value" :class="[
      'px-2 py-2 h-20 text-surface-500 appearance-none relative',
      'rounded border border-gray-300 outline-0',
      'focus:ring focus:ring-gray-700'
    ]" />
  </ArkFieldRoot>
</template>
