<script setup lang="ts">
import { Checkbox } from '@ark-ui/vue/checkbox';

const checked = defineModel<boolean>({ default: false })
const props = defineProps<{
  label?: string;
}>();
</script>

<template>
  <div class="flex gap-2 items-center">
    <input
      type="checkbox"
      v-model="checked"
    />
    <label v-if="label">{{ label }}</label>
  </div>
</template>
