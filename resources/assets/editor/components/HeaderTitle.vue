<script setup lang="ts">
import useI18n from '../composables/i18n';
import { useState } from '../state';

const { theme } = useState();
const { t } = useI18n();
</script>

<template>
  <div class="pl-2">
    <h1 class="font-medium text-base">{{ t('title') }}</h1>
    <h2 class="text-gray-700 text-xs">
      {{ theme?.name }} <span
        v-if="theme?.version"
        class="text-gray-500"
      >(v{{ theme.version }})</span>
    </h2>
  </div>
</template>
