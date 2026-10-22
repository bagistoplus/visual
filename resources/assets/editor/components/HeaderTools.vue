<script setup lang="ts">
import { useState } from '../state';
import { isBagistoTheme } from '../craftile/features/bagistoPreviewRefresh';

const editor = useCraftileEditor();
const { channel, locale, state } = useState();

// A theme whose sections Bagisto stores has no templates to switch between:
// its sections live on the home page and in the layout, and the merchant
// reaches every other page by browsing the preview.
const hasTemplates = !isBagistoTheme(state);

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
    <TemplateSelector v-if="hasTemplates" />

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