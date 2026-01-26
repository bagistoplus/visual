<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Popover } from '@ark-ui/vue/popover';
import { useState } from '../state';
import useI18n from '../composables/i18n';
import { toTitleCase } from '../utils/strings';

interface Props {
  field: PropertyField;
}

defineProps<Props>();
const { theme } = useState();
const { t } = useI18n();

const typographyPresets = computed(() => {
  if (!theme.value) {
    return {};
  }

  const settingId = theme.value.settingsSchema
    .flatMap((obj) => obj.settings)
    .find((setting) => setting.type === 'typography_presets')?.id;

  if (!settingId) {
    return {};
  }

  return theme.value.settings[settingId] || {};
});

const presetsAvailable = computed(() => {
  return Object.keys(typographyPresets.value).length > 0;
});

const model = defineModel<string | null>();
const opened = ref(false);

const selectedPreset = computed(() => {
  return model.value && typographyPresets.value
    ? (typographyPresets.value as Record<string, any>)[model.value as string]
    : null;
});

const selectedLabel = computed(() => {
  if (!model.value || !selectedPreset.value) {
    return null;
  }

  return selectedPreset.value.name || toTitleCase(model.value);
});

function onSelectPreset(id: string) {
  model.value = id;
  opened.value = false;
}
</script>

<template>
  <div>
    <label class="text-sm font-medium mb-1 text-gray-700">
      {{ field.label }}
    </label>
    <div v-if="presetsAvailable">
      <Popover.Root v-model:open="opened">
        <Popover.Trigger class="border border-zinc-300 rounded w-full flex justify-between h-10 px-3 items-center">
          <div
            v-if="selectedPreset"
            class="flex flex-1 gap-3"
          >
            <div class="flex-1 text-left text-zinc-700">{{ selectedLabel }}</div>
            <button
              class="ml-auto flex-none mr-1 p-1 rounded-lg hover:bg-zinc-100"
              @click.stop="model = null"
            >
              <i-heroicons-x-mark class="w-4 h-4" />
            </button>
          </div>
          <span v-else>{{ t('Select a typography preset') }}</span>
          <i-heroicons-chevron-up-down class="flex-none h-4 w-4" />
        </Popover.Trigger>

        <Popover.Positioner>
          <Popover.Content class="border border-zinc-300 rounded shadow-md w-[var(--reference-width)] z-50 bg-white -mt-1 overflow-y-auto max-h-[360px]">
            <div class="divide-y divide-zinc-200">
              <div
                v-for="(preset, id) in typographyPresets"
                :key="id"
                class="px-3 py-3 hover:bg-zinc-100 relative cursor-pointer"
                :class="{ 'bg-zinc-100': model === String(id) }"
                @click="onSelectPreset(String(id))"
              >
                <i-heroicons-check-circle-solid
                  v-if="model === String(id)"
                  class="w-5 h-5 absolute top-2 right-2 text-green-400"
                />
                <TypographyPresetPreview
                  :preset="preset"
                  :label="String(id)"
                />
              </div>
            </div>
          </Popover.Content>
        </Popover.Positioner>
      </Popover.Root>
    </div>

    <div
      v-else
      class="text-sm text-red-400"
    >
      {{ t('No typography presets defined in theme settings.') }}
    </div>
  </div>
</template>
