<script setup lang="ts">
import { Menu } from '@ark-ui/vue/menu';
import { Button } from '@craftile/editor/ui';
import { useState } from '../state';
import useI18n from '../composables/i18n';

const { t } = useI18n();
const { channels } = useState();

const selected = defineModel<string>();
const selectedLabel = computed(() => channels.value.find(c => c.code === selected.value)?.name);

function onSelect({ value }: { value: string }) {
  selected.value = value;
}
</script>

<template>
  <Menu.Root
    :positioning="{ gutter: 4 }"
    @select="onSelect"
    v-if="channels.length > 1"
  >
    <Menu.Trigger asChild>
      <Button>
        <i-heroicons-building-storefront class="inline w-4" />
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
            {{ t('Channels') }}
          </Menu.ItemGroupLabel>
          <Menu.Item
            v-for="c in channels"
            :key="c.code"
            :value="c.code"
            class="rounded cursor-pointer flex items-center h-9 px-3 gap-3 hover:bg-zinc-100"
          >
            {{ c.name }}
          </Menu.Item>
        </Menu.ItemGroup>
      </Menu.Content>
    </Menu.Positioner>
  </Menu.Root>
</template>