<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Dialog } from '@ark-ui/vue/dialog';
import { ColorSchemeDefintion } from '../types';

interface Props {
  field: PropertyField;
}

const props = defineProps<Props>();
const model = defineModel<Record<string, { id: string; tokens: ColorSchemeDefintion } | ColorSchemeDefintion>>();
const emit = defineEmits(['update:modelValue'])

const editingScheme = ref<string | null>(null);
const editModalOpen = ref(false);

const editingSchemeValue = computed({
  get() {
    if (!editingScheme.value || !model.value?.[editingScheme.value]) {
      return null;
    }
    const scheme = model.value[editingScheme.value];
    // If scheme has tokens property, return tokens, otherwise return scheme directly
    return 'tokens' in scheme ? scheme.tokens : scheme;
  },
  set(value: ColorSchemeDefintion | null) {
    if (!editingScheme.value || !value) return;

    const currentScheme = model.value![editingScheme.value];
    // If current scheme has tokens property, update tokens, otherwise update directly
    if ('tokens' in currentScheme) {
      currentScheme.tokens = value;
    } else {
      model.value![editingScheme.value] = value as any;
    }
  }
});

function onEditScheme(id: string) {
  editingScheme.value = id;
  editModalOpen.value = true;
}

function onUpdate() {
  emit('update:modelValue', model.value)
}

function onAddScheme() {
  const keys = Object.keys(model.value!);
  const id = `scheme-${keys.length + 1}`;

  model.value![id] = structuredClone(toRaw(model.value![keys[0]]));

  onUpdate();
  onEditScheme(id);
}
</script>

<template>
  <div>
    <label class="text-sm font-medium mb-1 text-gray-700">
      {{ field.label }}
    </label>
    <div class="grid grid-cols-2 gap-2 items-stretch mt-1">
      <template
        v-for="(scheme, id) in model"
        :key="id"
      >
        <ColorSchemePreview
          :id="id"
          :scheme="scheme"
          @click="onEditScheme(id)"
        />
      </template>
      <button
        class="flex-col border-2 border-dashed flex items-center justify-center min-h-20 rounded border-blue-400 text-blue-400 cursor-pointer"
        @click="onAddScheme"
      >
        <i-heroicons-plus class="h-6 w-6" />
        <span>Add scheme</span>
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
            <Dialog.Title class="capitalize">Editing {{ editingScheme?.replace('-', ' ') }}</Dialog.Title>
            <Dialog.CloseTrigger class="cursor-pointer rounded-lg p-0.5 text-neutral-700 hover:bg-neutral-300">
              <i-heroicons-x-mark class="w-5 h-5" />
            </Dialog.CloseTrigger>
          </header>
          <div class="flex-1 flex flex-col  min-h-0  overflow-y-auto p-4">
            <EditColorScheme
              v-if="editingScheme && editingSchemeValue"
              :id="editingScheme"
              v-model="editingSchemeValue"
              @update="onUpdate"
            />
          </div>
        </Dialog.Content>
      </Dialog.Positioner>
    </Dialog.Root>
  </div>
</template>
