<script setup lang="ts">
import { useState } from '../state';
import { Template } from '../types';

const { channel, locale } = useState();
</script>

<template>
  <div class="flex-1 flex justify-start items-center gap-2">
    <TemplateSelector @select="(t: Template) => console.log('change template', t)" />

    <ChannelSelector
      :model-value="channel"
      @update:model-value="console.log('change channel', $event)"
    />

    <LocaleSelector
      :channel="channel"
      :model-value="locale"
      @update:model-value="(locale: string) => console.log('change locale', locale)"
    />
  </div>
</template>