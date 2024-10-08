
<template>
  <div>
    <Button text plain severity="primary" @click="toggle" class="min-w-28 !text-left !focus:ring-0">
      <BuildingStorefrontIcon class="inline w-4 mr-2"/>
      {{ selectedLabel }}
      <ChevronDownIcon class="inline w-4 ml-2"/>
    </Button>

    <Popover ref="popover">
      <div class="gap-1 w-40 -m-4">
        <h2 class="font-medium text-sm pl-2 pb-1 mb-2 border-b">Channels</h2>
        <a
          v-for="channel in channels"
          href="#"
          class="flex items-center hover:bg-zinc-100 rounded-md px-2 py-1"
          @click="select(channel)"
        >
          {{ channel.name }}
        </a>
      </div>
    </Popover>
  </div>
</template>

<script setup lang="ts">
import type { Channel } from '../types';

const popover = useTemplateRef('popover');
const channels = window.ThemeEditor.channels;
const selected = defineModel();

const selectedLabel = computed(() => channels.find(c => c.code === selected.value)?.name);

onBeforeMount(() => {
  if (!selected.value) {
    selected.value = channels[0].code
  }
});

function select(channel: Channel) {
  selected.value = channel.code;
  popover.value.hide();
}

function toggle(event: any) {
  popover.value.toggle(event);
}
</script>
