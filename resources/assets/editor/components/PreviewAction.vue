<script setup lang="ts">
import { Button } from '@craftile/editor/ui';
import { useCraftileEditor } from '../composables/useCraftileEditor';
import { useState } from '../state';

const editor = useCraftileEditor()!;
const { state, theme } = useState();

function buildPreviewUrl(): string | null {
  // pageData.url is the page actually rendered in the iframe, previewUrl is only the last URL we
  // loaded, so it goes stale as soon as the user follows a link inside the preview.
  const base = state.pageData?.url || editor.preview.state.previewUrl;

  if (!base) {
    return null;
  }

  try {
    const url = new URL(base, window.location.origin);
    const themeCode = url.searchParams.get('_designMode') || theme.value?.code;

    if (!themeCode) {
      return null;
    }

    url.searchParams.delete('_designMode');
    url.searchParams.delete('_visual_render');
    url.searchParams.set('_draftPreview', themeCode);

    return url.href;
  } catch {
    return null;
  }
}

function openInNewTab() {
  const previewUrl = buildPreviewUrl();

  if (!previewUrl) {
    return;
  }

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
