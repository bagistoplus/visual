<script setup lang="ts">
import { useStore } from '../store';

const store = useStore();
const value = ref('desktop');
const viewModes = ref([
  { icon: 'ComputerDesktopIcon', value: 'desktop', label: 'Desktop' },
  { icon: 'DevicePhoneMobileIcon', value: 'mobile', label: 'Mobile' },
  { icon: 'ArrowsPointingOutIcon', value: 'fullscreen', label: 'Fullscreen' },
]);
</script>

<template>
  <div class="flex">
    <div class="w-[20.98rem] h-full items-center flex-none flex">
      <button class="hover:bg-gray-100 px-[0.85rem] focus:outline-none h-full" @click="$emit('exit')">
        <ArrowLeftEndOnRectangleIcon class="w-5"/>
      </button>

      <div class="flex-1 px-4 border-l border-r">
        <h1 class="font-medium">App title</h1>
        <h2 class="text-gray-700">Theme name</h2>
      </div>
    </div>
    <div class="flex flex-1 items-center justify-between">
      <div class="flex-1 flex justify-start pl-4">
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
        <SelectButton v-model="value" :options="viewModes" optionLabel="label" optionValue="value" aria-labelledby="custom">
          <template #option="{ option }">
            <ComputerDesktopIcon v-if="option.icon === 'ComputerDesktopIcon'" class="inline w-4 -mx-2"/>
            <DevicePhoneMobileIcon v-else-if="option.icon === 'DevicePhoneMobileIcon'" class="inline w-4 -mx-2" />
            <ArrowsPointingOutIcon v-else-if="option.icon === 'ArrowsPointingOutIcon'" class="inline w-4 -mx-2" />
          </template>
        </SelectButton>

        <div class="space-x-1">
            <Button link title="Undo" aria-label="Undo" class="!px-2 hover:bg-surface-100">
            <ArrowUturnLeftIcon class="inline w-4"/>
          </Button>
          <Button link title="Redo" aria-label="Redo" class="!px-2 hover:bg-surface-100">
            <ArrowUturnRightIcon class="inline w-4"/>
          </Button>
        </div>

        <Button label="Save" />
      </div>
    </div>
  </div>
</template>
