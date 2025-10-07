<script setup lang="ts">
import useI18n from '../composables/i18n';

const { t } = useI18n();
</script>

<template>
  <div class="h-full flex flex-col overflow-y-hidden">
    <div class="flex-none h-12 flex items-center border-b px-4">
      <h2>{{ t('Media') }}</h2>
    </div>
    <div class="flex-1 overflow-y-auto"></div>
  </div>
</template>