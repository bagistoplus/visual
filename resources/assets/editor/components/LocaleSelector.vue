<script setup lang="ts">
import { Menu } from '@ark-ui/vue/menu';
import { Button } from '@craftile/editor/ui';
import { useState } from '../state';
import useI18n from '../composables/i18n';

const props = defineProps<{ channel: string }>();

const { t } = useI18n();
const { localeInheritance } = useState();
const channels = window.editorConfig.channels || [];
const selected = defineModel();

const locales = computed(() => {
  let channel = channels.find(c => c.code === props.channel)
  if (!channel) {
    channel = channels[0];
  }

  return channel?.locales || [];
});
const selectedLabel = computed(() => locales.value.find(c => c.code === selected.value)?.name);
const defaultLocale = computed(() => {
  const channel = channels.find(c => c.code === props.channel) || channels[0];

  return channel?.default_locale || locales.value[0]?.code;
});

onBeforeMount(() => {
  if (!selected.value) {
    selected.value = defaultLocale.value
  }
});

watch(() => props.channel, (newChannel) => {
  const localeExists = !!locales.value.find(l => l.code === selected.value);
  if (!localeExists) {
    selected.value = defaultLocale.value;
  }
})
function onSelect({ value }: { value: string }) {
  selected.value = value;
}

function channelName(code: string): string {
  return channels.find(c => c.code === code)?.name || code;
}

function localeName(channel: string, locale: string): string {
  return channels
    .find(c => c.code === channel)
    ?.locales
    ?.find(l => l.code === locale)
    ?.name || locale;
}

function inheritanceLabel(locale: string): string | null {
  const parent = localeInheritance.value[locale];

  if (!parent) {
    return null;
  }

  const parentLocale = localeName(parent.parentChannel, parent.parentLocale);
  const parentContext = parent.parentChannel === props.channel
    ? parentLocale
    : `${channelName(parent.parentChannel)} / ${parentLocale}`;

  return `${t('Inherits')} ${parentContext}`;
}
</script>

<template>
  <Menu.Root
    :positioning="{ gutter: 4 }"
    v-if="locales && locales.length > 1"
    @select="onSelect"
  >
    <Menu.Trigger
      asChild
      class="min-w-32 py-2 appearance-none rounded-lg cursor-pointer inline-flex gap-3 outline-none relative select-none items-center justify-center hover:bg-gray-200"
    >
      <Button>
        <i-heroicons-globe-asia-australia class="inline w-4" />
        {{ selectedLabel }}
        <Menu.Indicator>
          <i-heroicons-chevron-down class="inline w-4" />
        </Menu.Indicator>
      </Button>
    </Menu.Trigger>
    <Menu.Positioner class="w-56">
      <Menu.Content class="pointer-events-none border shadow flex gap-1 p-1 flex-col outline-none rounded-md bg-white data-[state=open]:animate-fade-in">
        <Menu.ItemGroup class="flex flex-col">
          <Menu.ItemGroupLabel class="px-2.5 mb-1 text-zinc-700 text-semibold">
            {{ t('Locales') }}
          </Menu.ItemGroupLabel>
          <Menu.Item
            v-for="l in locales"
            :key="l.code"
            :value="l.code"
            class="rounded cursor-pointer flex flex-col items-start justify-center min-h-9 px-3 py-1.5 hover:bg-zinc-100"
          >
            <span>{{ l.name }}</span>
            <span
              v-if="inheritanceLabel(l.code)"
              class="text-[10px] text-zinc-500 inline-flex items-center gap-1 -mt-0.5"
            >
              <i-heroicons-arrow-turn-down-right class="w-2.5 h-2.5" />
              {{ inheritanceLabel(l.code) }}
            </span>
          </Menu.Item>
        </Menu.ItemGroup>
      </Menu.Content>
    </Menu.Positioner>
  </Menu.Root>
</template>
