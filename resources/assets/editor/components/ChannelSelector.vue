<script setup lang="ts">
  import { Menu } from '@ark-ui/vue/menu';

  const channels = window.ThemeEditor.channels;
  const selected = defineModel<string>();
  const selectedLabel = computed(() => channels.find(c => c.code === selected.value)?.name);

  function onSelect({ value }: { value: string }) {
    selected.value = value;
  }
</script>

<template>
  <Menu.Root @select="onSelect">
    <Menu.Trigger class="min-w-32 py-2 appearance-none rounded-lg cursor-pointer inline-flex gap-3 outline-none relative select-none items-center justify-center hover:bg-gray-200">
      <i-heroicons-building-storefront class="inline w-4" />
      {{ selectedLabel }}
      <Menu.Indicator>
        <i-heroicons-chevron-down class="inline w-4" />
      </Menu.Indicator>
    </Menu.Trigger>
    <Menu.Positioner class="w-56">
      <Menu.Content class="pointer-events-none border shadow flex gap-1 p-1 flex-col outline-none rounded bg-white data-[state=open]:animate-fade-in">
        <Menu.ItemGroup class="flex flex-col">
          <Menu.ItemGroupLabel class="px-2.5 mb-1 text-gray-700">
            {{ $t('Channels') }}
          </Menu.ItemGroupLabel>
          <Menu.Item
            v-for="c in channels"
            :key="c.code"
            :value="c.code"
            class="rounded cursor-pointer flex items-center h-9 px-3 gap-3 hover:bg-gray-200"
          >
            {{ c.name }}
          </Menu.Item>
        </Menu.ItemGroup>
      </Menu.Content>
    </Menu.Positioner>
  </Menu.Root>
</template>
