<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Dialog } from '@ark-ui/vue/dialog';
import useI18n from '../composables/i18n';
import TypographyPresetEditor from './TypographyPresetEditor.vue';
import TypographyPresetPreview from './TypographyPresetPreview.vue';

interface TypographyPresetData {
  fontFamily: string | null;
  fontStyle: 'normal' | 'italic';
  fontSize: string | Record<string, string>;
  lineHeight: string | Record<string, string>;
  letterSpacing: string;
  textTransform: 'none' | 'capitalize' | 'uppercase' | 'lowercase';
}

interface Props {
  field: PropertyField;
}

const props = defineProps<Props>();
const model = defineModel<Record<string, TypographyPresetData>>();

const { t } = useI18n();

const editingPreset = ref<string | null>(null);
const editModalOpen = ref(false);

const themePresetIds = computed(() => {
  return Object.keys(props.field.presets || {});
});

const editingPresetValue = computed({
  get() {
    if (!editingPreset.value || !model.value?.[editingPreset.value]) {
      return null;
    }
    return model.value[editingPreset.value];
  },
  set(value: TypographyPresetData | null) {
    if (!editingPreset.value || !value) return;
    model.value = { ...model.value, [editingPreset.value]: value };
  }
});

function onEditPreset(id: string) {
  editingPreset.value = id;
  editModalOpen.value = true;
}

function onAddPreset() {
  const keys = Object.keys(model.value!);
  const id = `typography-${keys.length + 1}`;

  model.value = {
    ...model.value,
    [id]: structuredClone(toRaw(model.value![keys[0]]))
  };

  onEditPreset(id);
}

function isCustomPreset(id: string): boolean {
  return !themePresetIds.value.includes(id);
}

function onDeletePreset(id: string) {
  if (!isCustomPreset(id)) {
    return;
  }

  if (!model.value) {
    return;
  }

  const { [id]: removed, ...rest } = model.value;
  model.value = rest;
}

function handleEditorDelete() {
  if (!editingPreset.value) {
    return;
  }

  onDeletePreset(editingPreset.value);

  editModalOpen.value = false;
}

const canDeleteCurrentPreset = computed(() => {
  return editingPreset.value !== null && isCustomPreset(editingPreset.value || '');
});
</script>

<template>
  <div>
    <label class="text-sm font-medium mb-1 text-gray-700">
      {{ field.label }}
    </label>
    <div class="grid gap-2 items-stretch mt-1">
      <template
        v-for="(preset, id) in model"
        :key="id"
      >
        <div class="relative group">
          <button
            class="w-full px-3 py-2 border border-zinc-300 rounded hover:bg-zinc-50 text-left cursor-pointer"
            @click="onEditPreset(String(id))"
          >
            <TypographyPresetPreview
              :preset="preset"
              :label="String(id)"
            />
          </button>
          <button
            v-if="isCustomPreset(String(id))"
            type="button"
            class="absolute top-2 right-2 p-1 rounded-lg bg-white border border-zinc-300 hover:bg-red-50 hover:border-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity z-10"
            @click.stop.prevent="onDeletePreset(String(id))"
            :title="t('Delete preset')"
          >
            <i-heroicons-trash class="w-3 h-3" />
          </button>
        </div>
      </template>
      <button
        class="flex border-2 border-dashed flex items-center justify-center text-sm gap-2 py-1 rounded border-blue-400 text-blue-400 cursor-pointer"
        @click="onAddPreset"
      >
        <i-heroicons-plus class="h-4 w-4" />
        <span>{{ t('Add preset') }}</span>
      </button>
    </div>

    <Dialog.Root
      v-model:open="editModalOpen"
      :modal="false"
      :close-on-interact-outside="false"
    >
      <Dialog.Positioner class="flex fixed z-50 top-14 left-14 bottom-0 w-75 items-center justify-center">
        <Dialog.Content class="bg-white shadow flex flex-col w-full h-full overflow-hidden">
          <header class="flex-none h-12 border-b border-neutral-200 flex gap-3 px-4 items-center justify-between">
            <Dialog.Title class="capitalize">{{ t('Editing') }} {{ editingPreset?.replace('-', ' ') }}</Dialog.Title>
            <Dialog.CloseTrigger class="cursor-pointer rounded-lg p-0.5 text-neutral-700 hover:bg-neutral-300">
              <i-heroicons-x-mark class="w-5 h-5" />
            </Dialog.CloseTrigger>
          </header>
          <div class="flex-1 flex flex-col min-h-0 overflow-y-auto p-4">
            <TypographyPresetEditor
              v-if="editingPreset && editingPresetValue"
              v-model="editingPresetValue"
              :can-delete="canDeleteCurrentPreset"
              @delete="handleEditorDelete"
            />
          </div>
        </Dialog.Content>
      </Dialog.Positioner>
    </Dialog.Root>
  </div>
</template>
