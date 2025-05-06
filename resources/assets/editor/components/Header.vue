<script setup lang="ts">
  import { ToggleGroup } from '@ark-ui/vue/toggle-group'
  import { Dialog } from '@ark-ui/vue/dialog';
  import { useStore } from '../store';
  import HeroiconsComputerDesktop from '~icons/heroicons/computer-desktop'
  import HeroiconsDevicePhoneMobile from '~icons/heroicons/device-phone-mobile'
  import HeroiconsArrowsPointingOut from '~icons/heroicons/arrows-pointing-out'
  import { Template } from '../types';

  const CONFIRM_PUBLISH_KEY = 'bagisto_visual_editor_confirm_publush';

  const emit = defineEmits<{
    (e: 'exit'): void;
    (e: 'channelChanged', channel: string): void;
    (e: 'localeChanged', locale: string): void;
    (e: 'undoHistory'): void;
    (e: 'redoHistory'): void;
    (e: 'publishTheme'): void;
    (e: 'changeTemplate', template: Template): void;
  }>();

  const props = defineProps<{
    canUndoHistory: boolean;
    canRedoHistory: boolean;
  }>();

  const store = useStore();
  const viewModeModel = defineModel<string>('viewMode', { default: 'desktop' });
  const viewModes = ref([
    { icon: HeroiconsComputerDesktop, value: 'desktop', label: 'Desktop' },
    { icon: HeroiconsDevicePhoneMobile, value: 'mobile', label: 'Mobile' },
    { icon: HeroiconsArrowsPointingOut, value: 'fullscreen', label: 'Fullscreen' },
  ]);

  const viewMode = computed<string[]>({
    get: () => [viewModeModel.value],
    set(value: string[]) {
      viewModeModel.value = value[0];
    }
  });

  const publishDialogActive = ref(false);
  const dontAskNextTime = ref(false);

  function onPublishClick() {
    if (null === localStorage.getItem(CONFIRM_PUBLISH_KEY)) {
      publishDialogActive.value = true;
      return;
    }

    emit('publishTheme');
  }

  function onConfirmPublish() {
    if (dontAskNextTime.value) {
      localStorage.setItem(CONFIRM_PUBLISH_KEY, 'true');
    }

    emit('publishTheme');
    publishDialogActive.value = false;
  }
</script>

<template>
  <div class="flex">
    <div class="w-[23.47rem] h-full items-center flex-none flex">
      <button
        class="hover:bg-gray-100 w-[3.45rem] flex justify-center items-center focus:outline-none h-full"
        @click="emit('exit')"
      >
        <i-heroicons-arrow-left-end-on-rectangle class="w-5" />
      </button>

      <div class="flex-1 px-4 border-l border-r">
        <h1 class="font-medium">{{ $t('title') }}</h1>
        <h2 class="text-gray-700 capitalize">
          {{ store.themeData.theme.replace('-', ' ') }}
        </h2>
      </div>
    </div>
    <div class="flex flex-1 items-center justify-between">
      <div class="flex-1 flex justify-start pl-4 gap-4">
        <TemplateSelector @select="(t: Template) => emit('changeTemplate', t)" />

        <ChannelSelector
          :model-value="store.themeData.channel"
          @update:model-value="(channel: string) => emit('channelChanged', channel)"
        />

        <LocaleSelector
          :channel="store.themeData.channel"
          :model-value="store.themeData.locale"
          @update:model-value="(locale: string) => emit('localeChanged', locale)"
        />
      </div>
      <div class="mr-4 flex space-x-4">
        <ToggleGroup.Root
          v-model="viewMode"
          class="rounded-lg bg-gray-200 p-1 flex gap-1 overflow-hidden"
        >
          <ToggleGroup.Item
            v-for="mode in viewModes"
            :value="mode.value"
            :key="mode.value"
            class="appearance-none cursor-pointer inline-flex items-center justify-center outline-none relative p-1 rounded data-[state=on]:bg-white"
          >
            <component
              :is="mode.icon"
              class="w-4"
            />
          </ToggleGroup.Item>
        </ToggleGroup.Root>

        <div class="flex items-center gap-1">
          <button
            :title="$t('Undo')"
            :aria-label="$t('Undo')"
            type="button"
            class="rounded px-2 py-1 hover:bg-gray-200"
            :class="{ 'pointer-events-none text-gray-300': !canUndoHistory }"
            @click="emit('undoHistory')"
          >
            <i-heroicons-arrow-uturn-left class="inline w-4 h-4" />
          </button>
          <button
            :title="$t('Redo')"
            :aria-label="$t('Redo')"
            type="button"
            class="rounded px-2 hover:bg-gray-200 py-1"
            :class="{ 'pointer-events-none text-gray-300': !canRedoHistory }"
            @click="emit('redoHistory')"
          >
            <i-heroicons-arrow-uturn-right class="inline w-4 h-4" />
          </button>
        </div>

        <button
          class="cursor-pointer border px-3 rounded-md shadow-sm bg-gray-700 text-white active:bg-gray-600 focus:ring focus:ring-gray-200 disabled:bg-gray-200 disabled:text-gray-400"
          :disabled="!canUndoHistory"
          @click="onPublishClick"
        >
          {{ $t('Publish') }}
        </button>
      </div>
    </div>
    <Dialog.Root
      v-model:open="publishDialogActive"
      lazyMount
    >
      <Teleport to="body">
        <Dialog.Backdrop class="fixed h-screen w-screen left-0 top-0 backdrop-blur-sm bg-gray-700/30" />
        <Dialog.Positioner class="flex fixed inset-0 items-center justify-center">
          <Dialog.Content class="bg-white shadow rounded-md flex flex-col w-full max-w-sm overflow-hidden">
            <div class="flex flex-col gap-8 p-6">
              <Dialog.Title class="text-lg font-semibold">
                {{ $t('Publish edits ?') }}
              </Dialog.Title>
              <Dialog.Description>
                {{ $t('publish_warning_line1') }}<br><br>
                {{ $t('publish_warning_line2') }}
              </Dialog.Description>

              <Checkbox
                v-model="dontAskNextTime"
                :label="$t('Don\'t ask next time')"
              />
              <div class="flex gap-6">
                <Dialog.CloseTrigger class="flex-1 border rounded py-1 outline-none hover:bg-gray-100 focus:ring focus:ring-gray-700">Cancel</Dialog.CloseTrigger>
                <button
                  class="flex-1 border rounded bg-gray-900 hover:bg-gray-950 text-white"
                  @click="onConfirmPublish"
                >{{ $t('Publish') }}</button>
              </div>
            </div>
          </Dialog.Content>
        </Dialog.Positioner>
      </Teleport>
    </Dialog.Root>
  </div>

</template>