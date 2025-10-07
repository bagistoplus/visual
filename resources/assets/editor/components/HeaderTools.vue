<script setup lang="ts">
import { useState } from '../state';

const editor = useCraftileEditor();
const { channel, locale, state } = useState();

function handleChannelChange(newChannel: string) {
  state.channel = newChannel;

  const channels = window.editorConfig.channels;
  const channelData = channels.find(c => c.code === newChannel);

  if (channelData && !channelData.locales.find(l => l.code === state.locale)) {
    state.locale = channelData.default_locale;
  }

  reloadPreview();
}

function handleLocaleChange(newLocale: string) {
  state.locale = newLocale;
  reloadPreview();
}

function reloadPreview() {
  const currentUrl = new URL(editor.preview.getFrame().src);
  currentUrl.searchParams.set('channel', state.channel);
  currentUrl.searchParams.set('locale', state.locale);
  editor.preview.loadUrl(currentUrl.href);
}
</script>

<template>
  <div class="flex-1 flex justify-start items-center gap-2">
    <TemplateSelector />

    <ChannelSelector
      :model-value="channel"
      @update:model-value="handleChannelChange"
    />

    <LocaleSelector
      :channel="channel"
      :model-value="locale"
      @update:model-value="handleLocaleChange"
    />
  </div>
</template>