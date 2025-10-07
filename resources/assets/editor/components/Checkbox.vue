<script setup lang="ts">
import { Checkbox } from '@ark-ui/vue/checkbox';

const checked = defineModel<boolean>({ default: false })
const props = defineProps<{
  label?: string;
}>();
</script>

<template>
  <label class="flex items-center mb-4">
    <input
      type="checkbox"
      class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 focus:ring-2"
      v-model="checked"
    >
    <span
      v-if="label"
      class="ms-2 text-sm font-medium text-gray-900"
    >
      {{ label }}
    </span>
  </label>
</template>