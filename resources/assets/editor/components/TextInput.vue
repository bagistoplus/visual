<script setup lang="ts">
const value = defineModel<string>();
const props = defineProps<{
  label?: string;
}>();
</script>

<template>
  <ArkFieldRoot class="flex flex-col gap-1.5">
    <ArkFieldLabel v-if="label" class="text-sm font-medium">
      {{ props.label }}
    </ArkFieldLabel>
    <ArkFieldInput v-model="value" :class="[
      'px-3 h-10 text-surface-500 ',
      'rounded border border-gray-300',
      'focus:outline-none focus:ring focus:ring-gray-700'
    ]" />
  </ArkFieldRoot>
</template>
