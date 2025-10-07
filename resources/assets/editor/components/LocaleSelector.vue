<script setup lang="ts">
import { Menu } from '@ark-ui/vue/menu';
import { Button } from '@craftile/editor/ui';
import useI18n from '../composables/i18n';

const props = defineProps<{ channel: string }>();

const { t } = useI18n();
const channels = window.editorConfig.channels || [];
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
function onSelect({ value }: { value: string }) {
  selected.value = value;
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
      <Menu.Content class="pointer-events-none border shadow flex gap-1 p-1 flex-col outline-none rounded bg-white data-[state=open]:animate-fade-in">
        <Menu.ItemGroup class="flex flex-col">
          <Menu.ItemGroupLabel class="px-2.5 mb-1 text-zinc-700">
            {{ t('Locales') }}
          </Menu.ItemGroupLabel>
          <Menu.Item
            v-for="l in locales"
            :key="l.code"
            :value="l.code"
            class="rounded cursor-pointer flex items-center h-9 px-3 gap-3 hover:bg-zinc-100"
          >
            {{ l.name }}
          </Menu.Item>
        </Menu.ItemGroup>
      </Menu.Content>
    </Menu.Positioner>
  </Menu.Root>
</template>