<script setup lang="ts">
import type { PropertyField } from '@craftile/types';

defineProps<{
  field: PropertyField & { content?: string };
}>();
</script>

<template>
  <div
    class="prose prose-sm max-w-none text-xs text-gray-500 prose-p:my-1 prose-a:text-primary prose-a:underline prose-ul:my-1 prose-li:my-0"
    v-html="field.content"
  ></div>
</template>
