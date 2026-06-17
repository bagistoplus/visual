<script setup lang="ts">
import type { CraftileEditor } from '@craftile/editor';
import { computed, onBeforeUnmount, onMounted, watch } from 'vue';

import { useState } from '../state';

const props = defineProps<{ editor: CraftileEditor }>();
const { state } = useState();

const textWidths = [148, 176, 156, 132, 168, 144, 124];
const isVisible = computed(() => state.previewLoading && props.editor.ui.state.activeSidebarPanel === 'layers');

function updateContainerVisibility(visible: boolean) {
  const container = document.querySelector<HTMLElement>('[data-visual-blocks-tree-loading-overlay="true"]');

  if (!container) {
    return;
  }

  container.style.background = visible ? '#fff' : '';
  container.style.pointerEvents = visible ? 'auto' : 'none';
}

onMounted(() => {
  updateContainerVisibility(isVisible.value);
});

onBeforeUnmount(() => {
  updateContainerVisibility(false);
});

watch(isVisible, updateContainerVisibility);
</script>

<template>
  <div
    v-if="isVisible"
    class="__craftile blocks-tree-loading-overlay"
    aria-busy="true"
    aria-label="Loading preview blocks"
  >
    <div class="blocks-tree-loading-overlay__header">
      <div class="blocks-tree-loading-overlay__title" />
      <div class="blocks-tree-loading-overlay__button" />
    </div>

    <div class="blocks-tree-loading-overlay__region">
      <div class="blocks-tree-loading-overlay__region-title" />
      <div
        v-for="index in 7"
        :key="index"
        class="blocks-tree-loading-overlay__row"
        :style="{ paddingLeft: `${((index - 1) % 3) * 18}px` }"
      >
        <div class="blocks-tree-loading-overlay__icon" />
        <div
          class="blocks-tree-loading-overlay__text"
          :style="{ width: `${textWidths[index - 1]}px` }"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.blocks-tree-loading-overlay {
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: auto;
  background: rgb(255 255 255);
  overflow: hidden;
}

.blocks-tree-loading-overlay__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 16px;
  border-bottom: 1px solid rgb(229 231 235);
}

.blocks-tree-loading-overlay__region {
  padding: 12px 16px;
}

.blocks-tree-loading-overlay__title,
.blocks-tree-loading-overlay__button,
.blocks-tree-loading-overlay__region-title,
.blocks-tree-loading-overlay__icon,
.blocks-tree-loading-overlay__text {
  background: linear-gradient(90deg, rgb(229 231 235) 25%, rgb(243 244 246) 37%, rgb(229 231 235) 63%);
  background-size: 400% 100%;
  border-radius: 4px;
  animation: blocks-tree-loading-shimmer 1.2s ease-in-out infinite;
}

.blocks-tree-loading-overlay__title {
  width: 88px;
  height: 18px;
}

.blocks-tree-loading-overlay__button {
  width: 24px;
  height: 24px;
}

.blocks-tree-loading-overlay__region-title {
  width: 112px;
  height: 14px;
  margin: 2px 0 10px;
}

.blocks-tree-loading-overlay__row {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 34px;
}

.blocks-tree-loading-overlay__icon {
  width: 16px;
  height: 16px;
  flex: none;
}

.blocks-tree-loading-overlay__text {
  height: 13px;
  flex: none;
}

@keyframes blocks-tree-loading-shimmer {
  0% {
    background-position: 100% 0;
  }

  100% {
    background-position: 0 0;
  }
}
</style>
