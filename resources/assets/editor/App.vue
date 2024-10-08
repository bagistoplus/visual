<script lang="ts" setup>
import type { Section, ThemeData } from './types'
import { useStore } from './store';

const store = useStore();
const storefrontUrl = window.ThemeEditor.storefrontUrl;

const previewIframe = useTemplateRef('previewer');

const messageHandlers: Record<string, Function> = {
  initialize(data: {availableSections: Record<string, Section>; themeData: ThemeData }) {
    store.setThemeData(data.themeData);
    store.setAvailableSections(data.availableSections);
  },

  'moveSectionUp': store.moveSectionUp,
  'moveSectionDown': store.moveSectionDown,
  'toggleSection': store.toggleSection,
  'removeSection': store.removeSection,
};

window.addEventListener('message', (event) => {
  const { data } = event;

  if (data.type && messageHandlers[data.type]) {
    messageHandlers[data.type](data.data);
  }
});

onMounted(() => {
  store.setPreviewIframe(previewIframe.value as HTMLIFrameElement);
});

function onExit() {
  window.location.href = window.ThemeEditor.routes.themesIndex;
}
</script>

<template>
  <div class="h-screen w-full flex flex-col">
    <Header class="h-14 border-b flex-none" @exit="onExit"/>
    <div class="flex-1 bg-gray-100 flex overflow-y-hidden">
      <div class="w-12 flex-none border-r bg-white">
        <ActionBar/>
      </div>
      <aside class="flex-none w-72 border-r bg-white flex overflow-y-hidden">
        <RouterView/>
      </aside>
      <div class="flex-1 flex justify-center items-center p-4">
        <iframe
          ref="previewer"
          frameborder="0"
          class="transition-all shadow bg-white"
          :style="{height:'100%', width: '100%'}"
          :src="storefrontUrl"
        ></iframe>
      </div>
    </div>
  </div>
</template>
