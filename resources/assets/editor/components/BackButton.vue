<script setup lang="ts">
import { Button } from '@craftile/editor/ui';

function onBackClick() {
  window.location.assign(window.editorConfig.routes.themesIndex);
}
</script>

<template>
  <Button
    square
    title="Back"
    @click="onBackClick"
  >
    <i-heroicons-arrow-left class="text-zinc-600" />
  </Button>
</template>
