<script setup lang="ts">
import { Accordion } from '@ark-ui/vue';
import { PropertyField } from '@craftile/editor/ui';
import { debounce } from 'perfect-debounce';
import useI18n from '../composables/i18n';
import { useState } from '../state';
import { persistThemeSettings as persistThemeSettingsApi } from '../api';
import { CRAFTILE_EDITOR } from '../plugin';

const { t } = useI18n();
const { theme } = useState();
const editor = inject<any>(CRAFTILE_EDITOR);

// Accumulator for pending setting changes
const pendingUpdates = ref<Record<string, any>>({});

const persistThemeSettings = debounce(async () => {
  if (!editor || Object.keys(pendingUpdates.value).length === 0) return;

  // Capture current updates and clear accumulator
  const updates = { ...pendingUpdates.value };
  pendingUpdates.value = {};

  const request = persistThemeSettingsApi(updates);

  request.onSuccess((result) => {
    if (result?.effects?.html) {
      editor.preview.sendMessage('page.refresh', {
        html: result.effects.html,
      });
    } else {
      // Full page reload if no partial updates
      editor.preview.reload();
    }
  });

  request.onError((error) => {
    console.error('Failed to persist theme settings:', error);
    editor.ui.toast({
      type: 'error',
      title: 'Failed to save theme settings',
    });
  });

  await request.execute();
}, 500);

const updateSetting = (id: string, value: any) => {
  if (!theme.value) return;

  // Update local state immediately for UI responsiveness
  theme.value.settings[id] = value;

  // Accumulate the change in pending updates
  pendingUpdates.value[id] = value;

  // Trigger debounced save (will batch all changes in 500ms window)
  persistThemeSettings();
};
</script>

<template>
  <div class="h-full flex flex-col overflow-y-hidden z-[100]">
    <div class="flex-none h-12 flex items-center border-b px-4">
      <h2>{{ t('Theme Settings') }}</h2>
    </div>
    <div class="flex-1 overflow-y-auto">
      <div v-if="theme?.settingsSchema && theme.settingsSchema.length > 0">
        <Accordion.Root
          :value="theme.settingsSchema[0]?.name"
          collapsible
        >
          <Accordion.Item
            v-for="group in theme.settingsSchema"
            :key="group.name"
            :value="group.name"
            class="border-b"
          >
            <Accordion.ItemTrigger class="w-full bg-white z-10 cursor-pointer px-4 py-3 font-medium text-sm hover:bg-gray-50 flex items-center justify-between text-zinc-700">
              <span>{{ group.name }}</span>
              <Accordion.ItemIndicator class="text-zinc-400 transition-transform duration-200 data-[state=open]:rotate-180">
                <i-heroicons-chevron-down class="w-3 h-3" />
              </Accordion.ItemIndicator>
            </Accordion.ItemTrigger>
            <Accordion.ItemContent class="px-4 py-3 space-y-4">
              <template
                v-for="setting in group.settings"
                :key="setting.id"
              >
                <div
                  v-if="setting.type === 'header'"
                  class="border-t pt-2 mb-1"
                >
                  <h3 class="text-sm font-medium">{{ setting.label }}</h3>
                </div>
                <PropertyField
                  v-else
                  :field="setting"
                  :model-value="theme?.settings?.[setting.id] ?? setting.default"
                  @update:model-value="updateSetting(setting.id, $event)"
                />
              </template>
            </Accordion.ItemContent>
          </Accordion.Item>
        </Accordion.Root>
      </div>
      <div
        v-else
        class="p-4 text-sm text-gray-500 text-center"
      >
        {{ t('No theme settings available') }}
      </div>
    </div>
  </div>
</template>
