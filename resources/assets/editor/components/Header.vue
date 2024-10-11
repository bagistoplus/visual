<script setup lang="ts">
import { ToggleGroup } from '@ark-ui/vue/toggle-group'
import { useStore } from '../store';
import { ArrowsPointingOutIcon, ComputerDesktopIcon, DevicePhoneMobileIcon } from '@heroicons/vue/24/outline';

const emit = defineEmits<{
  (e: 'exit'): void;
}>();

const store = useStore();
const viewMode = ref('desktop');
const viewModes = ref([
  { icon: ComputerDesktopIcon, value: 'desktop', label: 'Desktop' },
  { icon: DevicePhoneMobileIcon, value: 'mobile', label: 'Mobile' },
  { icon: ArrowsPointingOutIcon, value: 'fullscreen', label: 'Fullscreen' },
]);
</script>

<template>
  <div class="flex">
    <div class="w-[21.45rem] h-full items-center flex-none flex">
      <button class="hover:bg-gray-100 w-[3.45rem] flex justify-center items-center focus:outline-none h-full" @click="emit('exit')">
        <ArrowLeftEndOnRectangleIcon class="w-5"/>
      </button>

      <div class="flex-1 px-4 border-l border-r">
        <h1 class="font-medium">App title</h1>
        <h2 class="text-gray-700">Theme name</h2>
      </div>
    </div>
    <div class="flex flex-1 items-center justify-between">
      <div class="flex-1 flex justify-start pl-4 gap-4">
        <TemplateSelector/>

        <ChannelSelector
          v-model="store.themeData.channel"
        />

        <LocaleSelector
          :channel="store.themeData.channel"
          v-model="store.themeData.locale"
        />
      </div>
      <div class="mr-4 flex space-x-4">
        <ToggleGroup.Root :model-value="[viewMode]" class="rounded-lg bg-gray-200 p-1 flex gap-1 overflow-hidden">
          <ToggleGroup.Item v-for="mode in viewModes" :value="mode.value" class="appearance-none cursor-pointer inline-flex items-center justify-center outline-none relative p-1 rounded data-[state=on]:bg-white">
            <component :is="mode.icon" class="w-4"/>
          </ToggleGroup.Item>
        </ToggleGroup.Root>

        <div class="flex items-center gap-1">
          <button link title="Undo" aria-label="Undo" class="!px-2 hover:bg-surface-100">
            <ArrowUturnLeftIcon class="inline w-4"/>
          </button>
          <button link title="Redo" aria-label="Redo" class="!px-2 hover:bg-surface-100">
            <ArrowUturnRightIcon class="inline w-4"/>
          </button>
        </div>

        <button class="cursor-pointer border px-3 rounded-lg shadow bg-gray-700 text-white disabled:bg-gray-200 disabled:text-gray-400">
          Save
        </button>
      </div>
    </div>
  </div>
</template>
