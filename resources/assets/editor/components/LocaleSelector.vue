<template>
  <div>
    <Button text plain severity="primary" @click="toggle" class="min-w-28 !text-left !focus:ring-0">
      <GlobeAsiaAustraliaIcon class="inline w-4 mr-2"/>
        {{ selectedLabel }}
      <ChevronDownIcon class="inline w-4 ml-2"/>
    </Button>

    <Popover ref="popover">
      <div class="gap-1 w-40 -m-4">
        <h2 class="font-medium text-sm pl-2 pb-1 mb-2 border-b">Locales</h2>
        <a
          v-for="locale in locales"
          href="#"
          class="flex items-center hover:bg-zinc-100 rounded-md px-2 py-1"
          @click="select(locale)"
        >
          {{ locale.name }}
        </a>
      </div>
    </Popover>
  </div>
</template>

<script setup lang="ts">
import type { Locale } from '../types';
const props = defineProps<{channel: string }>();

const popover = useTemplateRef('popover');
const channels = window.ThemeEditor.channels;
const selected = defineModel();

const locales = computed(() => {
  let channel = channels.find(c => c.code === props.channel)
  if (!channel) {
    channel = channels[0];
  }

  return channel.locales;
});
const selectedLabel = computed(() => locales.value.find(c => c.code === selected.value)?.name);

onBeforeMount(() => {
  if (!selected.value) {
    selected.value = locales.value[0].code
  }
});

watch(() => props.channel, (newChannel) => {
  const localeExists = !!locales.value.find(l => l.code === selected.value);
  if (!localeExists) {
    selected.value = locales.value[0].code;
  }
})

function select(locale: Locale) {
  selected.value = locale.code;
  popover.value.hide();
}

function toggle(event: any) {
  popover.value.toggle(event);
}
</script>
