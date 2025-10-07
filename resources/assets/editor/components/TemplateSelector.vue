<script setup lang="ts">
import { Menu } from '@ark-ui/vue/menu';
import { Button } from '@craftile/editor/ui';

import { useState } from '../state';

const editor = useCraftileEditor();
const { currentTemplate, templates, theme, channel, locale } = useState();

function onSelect({ value }: { value: string }) {
  const template = templates.value.find(t => t.template === value);

  if (template) {
    const url = new URL(template.previewUrl);
    url.searchParams.set('_designMode', theme.value!.code as string);
    url.searchParams.set('channel', channel.value);
    url.searchParams.set('locale', locale.value);
    editor.preview.loadUrl(url.href);
  }
}
</script>

<template>
  <Menu.Root
    @select="onSelect"
    :positioning="{ gutter: 4 }"
  >
    <Menu.Trigger asChild>
      <Button>
        <template v-if="currentTemplate">
          <span v-html="currentTemplate.icon"></span>
          {{ currentTemplate.label }}
        </template>
        <template v-else-if="templates.length > 0">
          <span v-html="templates[0].icon"></span>
          {{ templates[0].label }}
        </template>
        <template v-else>
          Select Template
        </template>
        <Menu.Indicator>
          <i-heroicons-chevron-down class="inline w-4" />
        </Menu.Indicator>
      </Button>
    </Menu.Trigger>
    <Menu.Positioner class="w-64">
      <Menu.Content class="pointer-events-none border shadow flex gap-1 p-1 flex-col outline-none rounded bg-white data-[state=open]:animate-fade-in">
        <template
          v-for="t in templates"
          :key="t.template"
        >
          <Menu.Separator v-if="t.template === '__separator__'" />
          <Menu.Item
            v-else
            :value="t.template"
            class="rounded cursor-pointer flex items-center h-9 px-3 gap-3 hover:bg-gray-200"
          >
            <span v-html="t.icon"></span>
            {{ t.label }}
          </Menu.Item>
        </template>
      </Menu.Content>
    </Menu.Positioner>
  </Menu.Root>
</template>