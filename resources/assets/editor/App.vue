<script lang="ts" setup>
  import type { SettingsSchema, Template, ThemeData } from './types'
  import { useStore } from './store';
  import { useNProgress } from '@vueuse/integrations/useNProgress.mjs';

  const router = useRouter();
  const store = useStore();
  const nprogress = useNProgress();
  const storefrontUrl = window.ThemeEditor.storefrontUrl;

  const previewIframe = useTemplateRef('previewer');

  const viewMode = ref('desktop');
  const iframeStyle = computed(() => {
    if (viewMode.value !== "mobile") {
      return "width: 100%; height: 100%";
    }

    return "width: 375px; height: 100%";
  });

  const messageHandlers: Record<string, Function> = {
    initialize(data: { themeData: ThemeData, templates: Template[], settingsSchema: SettingsSchema }) {
      const templateChanged = store.themeData.template !== data.themeData.template;

      store.setThemeData(data.themeData);
      store.setSettingsSchema(data.settingsSchema);
      store.setTemplates(data.templates);
      store.setAvailableSections(window.ThemeEditor.availableSections);
      store.setPreviewIframeReady()

      if (templateChanged) {
        router.replace('/');
      }

      nprogress.done();
    },

    usedColors(colors: string[]) {
      Object.assign(store.usedColors, colors);
    },

    'section:move-up': store.moveSectionUp,
    'section:move-down': store.moveSectionDown,
    'section:toggle': store.toggleSection,
    'section:remove': store.removeSection,
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

  function onChannelChanged(channel: string) {
    if (!previewIframe.value) {
      return;
    }

    const url = new URL(window.ThemeEditor.storefrontUrl)
    url.searchParams.set('channel', channel);

    nprogress.start();
    previewIframe.value!.contentWindow?.location.replace(url.href);
    store.redoHistory();

    router.replace('/');
  }

  function onLocaleChanged(locale: string) {
    if (!previewIframe.value) {
      return;
    }

    const url = new URL(window.ThemeEditor.storefrontUrl)
    url.searchParams.set('locale', locale);
    url.searchParams.set('channel', store.themeData.channel);

    nprogress.start();
    previewIframe.value!.contentWindow?.location.replace(url.href);
    store.resetHistory();
    router.replace('/');
  }

  function onChangeTemplate(template: Template) {
    if (!previewIframe.value) {
      return;
    }

    const url = new URL(template.previewUrl)
    url.searchParams.set('locale', store.themeData.locale);
    url.searchParams.set('channel', store.themeData.channel);
    url.searchParams.set('_designMode', store.themeData.theme);

    nprogress.start();
    previewIframe.value!.contentWindow?.location.replace(url.href);
    store.resetHistory();
    router.replace('/');
  }
</script>

<template>
  <div class="h-screen w-full flex flex-col">
    <Header
      v-model:viewMode="viewMode"
      class="h-14 border-b flex-none"
      :can-undo-history="store.canUndoHistory"
      :can-redo-history="store.canRedoHistory"
      @exit="onExit"
      @channelChanged="onChannelChanged"
      @localeChanged="onLocaleChanged"
      @undoHistory="store.undoHistory()"
      @redoHistory="store.redoHistory()"
      @publish-theme="store.publishTheme()"
      @change-template="onChangeTemplate"
    />

    <div class="flex-1 bg-gray-100 flex overflow-y-hidden">
      <div
        v-show="viewMode !== 'fullscreen'"
        class="w-14 flex-none border-r bg-white transition-all shado"
      >
        <ActionBar />
      </div>
      <aside
        v-show="viewMode !== 'fullscreen'"
        class="flex-none w-80 border-r bg-white flex overflow-y-hidden transition-all shadow"
      >
        <RouterView />
      </aside>
      <div class="flex-1 flex justify-center items-center p-4">
        <iframe
          ref="previewer"
          frameborder="0"
          class="transition-all shadow bg-white"
          :src="storefrontUrl"
          :style="iframeStyle"
        ></iframe>
      </div>
    </div>
  </div>
</template>