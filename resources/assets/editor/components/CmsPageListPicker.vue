<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Button } from '@craftile/editor/ui';
import { Dialog } from '@ark-ui/vue/dialog';
import { CmsPage } from '../types';
import { useState } from '../state';
import useI18n from '../composables/i18n';

interface Props {
  field: PropertyField;
}

defineProps<Props>();

const { t } = useI18n();
const { getCmsPage } = useState();
const model = defineModel<number[]>({ default: () => [] });
const opened = ref(false);

const selectedPages = computed<CmsPage[]>(() =>
  (model.value ?? [])
    .map(id => getCmsPage(id))
    .filter((p): p is CmsPage => !!p)
);

function onSelect(page: CmsPage) {
  model.value = [...(model.value ?? []), page.id];
}

function onDeselect(page: CmsPage) {
  model.value = (model.value ?? []).filter(id => id !== page.id);
}

const currentValue = ref<number[]>([]);

function onOpenChange() {
  currentValue.value = [...(model.value ?? [])];
}

function onCancel() {
  model.value = [...currentValue.value];
  opened.value = false;
}

function onConfirm() {
  opened.value = false;
}
</script>

<template>
  <div>
    <label
      v-if="field.label"
      class="text-sm block mb-1 font-medium text-gray-700"
    >
      {{ field.label }}
    </label>
    <Dialog.Root
      v-model:open="opened"
      :modal="false"
      :close-on-interact-outside="false"
      @open-change="onOpenChange"
    >
      <div class="border rounded">
        <ul
          v-if="selectedPages.length"
          class="divide-y text-sm"
        >
          <li
            v-for="page in selectedPages.slice(0, 3)"
            :key="page.id"
            class="px-3 py-0.5 flex items-center gap-2"
          >
            <i-mdi-file-document-outline class="w-4 h-3 flex-none text-zinc-500" />
            <span class="flex-1 w-0 truncate">{{ page.page_title }}</span>
          </li>
          <li
            v-if="selectedPages.length > 3"
            class="px-3 py-0.5 text-zinc-500 text-sm"
          >
            + {{ selectedPages.length - 3 }} {{ t('more items') }}
          </li>
        </ul>
        <Dialog.Trigger
          class="flex items-center px-3 h-10 gap-4 w-full text-sm hover:bg-neutral-50"
          :class="{ 'border-t': selectedPages.length }"
        >
          <span class="flex-1 text-left">
            {{ selectedPages.length ? t('Change') : t('Select pages') }}
          </span>
          <i-heroicons-chevron-up-down class="flex-none w-4 h-4 text-zinc-500" />
        </Dialog.Trigger>
      </div>
      <Dialog.Positioner class="flex absolute z-50 inset-0 w-full h-full items-center justify-center">
        <Dialog.Content class="bg-white shadow flex flex-col w-full h-full overflow-hidden">
          <header class="flex-none h-12 border-b border-neutral-200 flex gap-3 px-4 items-center justify-between">
            <Dialog.Title>{{ field.label || t('Select pages') }}</Dialog.Title>
            <button
              class="cursor-pointer rounded-lg p-0.5 text-neutral-700 hover:bg-neutral-300"
              @click="onCancel"
            >
              <i-heroicons-x-mark class="w-5 h-5" />
            </button>
          </header>
          <div class="flex-1 min-h-0 flex flex-col overflow-hidden">
            <CmsPageListbox
              :model-value="selectedPages"
              multiple
              class="flex-1 min-h-0"
              @select="onSelect"
              @deselect="onDeselect"
            />
            <div class="flex-none border-t px-3 py-1.5 text-xs font-medium text-zinc-500 uppercase tracking-wide bg-zinc-50">
              {{ t('Current selection') }}
            </div>
            <div
              v-if="!selectedPages.length"
              class="flex-none flex items-center justify-center min-h-32 px-3 py-4 text-sm text-zinc-400"
            >
              {{ t('No selection') }}
            </div>
            <ul
              v-else
              class="divide-y min-h-32 max-h-72 overflow-y-auto text-sm flex-none"
            >
              <li
                v-for="page in selectedPages"
                :key="page.id"
                class="flex items-center gap-3 px-3 py-2"
              >
                <i-mdi-file-document-outline class="w-4 h-4 flex-none text-zinc-700" />
                <span class="flex-1 w-0 truncate">{{ page.page_title }}</span>
                <button
                  class="flex-none rounded-lg hover:bg-neutral-200 p-1"
                  @click.prevent="onDeselect(page)"
                >
                  <i-heroicons-x-mark class="w-4 h-4" />
                </button>
              </li>
            </ul>
          </div>
          <footer class="flex-none flex items-center gap-3 p-3 justify-end h-12 border-t border-neutral-200">
            <Button @click="onCancel">{{ t('Cancel') }}</Button>
            <Button
              variant="primary"
              @click="onConfirm"
            >
              {{ t('Confirm') }}
            </Button>
          </footer>
        </Dialog.Content>
      </Dialog.Positioner>
    </Dialog.Root>
  </div>
</template>
