<script setup lang="ts">
import { Button } from '@craftile/editor/ui';
import useI18n from '../composables/i18n';

const { t } = useI18n();
const editor = useCraftileEditor()!;

function openInNewTab() {
  const previewUrl = editor.preview.state.previewUrl;
  window.open(previewUrl, '_blank');
}
</script>

<template>
  <Button
    variant="secondary"
    square
    @click="openInNewTab"
    title="Preview in new tab"
  >
    <i-heroicons-eye />
  </Button>
</template>