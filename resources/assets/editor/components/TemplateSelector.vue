<script setup lang="ts">
import { Popover } from '@ark-ui/vue/popover';
import { Button } from '@craftile/editor/ui';

import { useState, type TemplateVariantType } from '../state';
import type { Template } from '../types';
import useI18n from '../composables/i18n';
import { isCustomTemplateVariant, useTemplateSelection } from '../composables/useTemplateSelection';

const editor = useCraftileEditor();
const { t } = useI18n();
const { currentTemplate, templates, state } = useState();
const { selectTemplate } = useTemplateSelection();

const isOpen = ref(false);
const activePanel = ref<TemplateVariantType | null>(null);
const variantTypes = ['product', 'category', 'page'] as const;

watch(isOpen, (open) => {
  if (!open) {
    activePanel.value = null;
  }
});

const rootTemplates = computed(() => templates.value.filter((template) => !isCustomTemplateVariant(template)));

const currentIcon = computed(() => currentTemplate.value?.icon || templates.value[0]?.icon || '');
const currentLabel = computed(() => currentTemplate.value?.label || templates.value[0]?.label || 'Select template');

function variantTemplates(type: TemplateVariantType) {
  return templates.value.filter((template) => template.type === type && template.template !== type);
}

function defaultTemplate(type: TemplateVariantType) {
  return templates.value.find((template) => template.template === type);
}

function defaultBaseTemplate(type: TemplateVariantType) {
  const template = defaultTemplate(type);

  return template?.isJsonTemplate ? template : null;
}

function isVariantType(type: unknown): type is TemplateVariantType {
  return typeof type === 'string' && variantTypes.includes(type as TemplateVariantType);
}

function variantType(template: Template): TemplateVariantType | null {
  return template.supportsVariants && isVariantType(template.type) ? template.type : null;
}

function opensVariantPanel(template: Template) {
  return Boolean(variantType(template));
}

function openVariantPanel(template: Template) {
  const type = variantType(template);

  if (type) {
    activePanel.value = type;
  }
}

function onSelectTemplate(template: Template) {
  if (!selectTemplate(template)) {
    return;
  }

  isOpen.value = false;
  activePanel.value = null;
}

function openCreate(type: TemplateVariantType) {
  state.templateForm.type = type;
  state.templateForm.name = '';
  state.templateForm.basedOn = defaultBaseTemplate(type) ? type : '__empty__';
  state.templateForm.error = '';
  state.templateForm.isSubmitting = false;
  isOpen.value = false;
  editor?.ui.openModal('create-template');
}

function panelBaseTemplate(type: TemplateVariantType) {
  return templates.value.find((template) => template.supportsVariants && template.type === type);
}

function defaultTemplateLabel(type: TemplateVariantType) {
  return t(`Default ${type}`);
}

</script>

<template>
  <div class="relative">
    <Popover.Root
      v-model:open="isOpen"
      :positioning="{ gutter: 4, strategy: 'fixed', placement: 'bottom-start' }"
    >
      <Popover.Trigger as-child>
        <Button>
          <span
            v-if="currentIcon"
            v-html="currentIcon"
          ></span>
          {{ currentLabel }}
          <i-heroicons-chevron-down class="inline w-4" />
        </Button>
      </Popover.Trigger>

      <Popover.Positioner class="w-72 !z-50">
        <Popover.Content class="max-h-112 overflow-y-auto rounded-md border bg-white p-1 shadow-lg outline-none">
          <template v-if="!activePanel">
            <template
              v-for="(template, index) in rootTemplates"
              :key="`${template.template}-${index}`"
            >
              <div
                v-if="template.template === '__separator__'"
                class="my-1 border-t"
              ></div>

              <button
                v-else-if="opensVariantPanel(template)"
                type="button"
                class="flex h-10 w-full items-center justify-between rounded-md px-3 text-left text-sm hover:bg-gray-100"
                @click="openVariantPanel(template)"
              >
                <span class="flex items-center gap-3">
                  <span v-html="template.icon"></span>
                  {{ template.label }}
                </span>

                <i-heroicons-chevron-right class="w-4" />
              </button>

              <button
                v-else
                type="button"
                class="flex h-10 w-full items-center gap-3 rounded-md px-3 text-left text-sm hover:bg-gray-100"
                @click="onSelectTemplate(template)"
              >
                <span v-html="template.icon"></span>
                {{ template.label }}
              </button>
            </template>
          </template>

          <template v-else>
            <button
              type="button"
              class="mb-1 flex h-10 w-full items-center gap-3 rounded-md px-3 text-left text-sm font-medium hover:bg-gray-100"
              @click="activePanel = null"
            >
              <i-heroicons-chevron-left class="w-4" />
              {{ panelBaseTemplate(activePanel)?.label || activePanel }}
            </button>

            <button
              v-if="defaultTemplate(activePanel)"
              type="button"
              class="flex min-h-10 w-full items-center gap-3 rounded-md px-3 text-left text-sm hover:bg-gray-100"
              @click="onSelectTemplate(defaultTemplate(activePanel)!)"
            >
              <span v-html="defaultTemplate(activePanel)!.icon"></span>
              <span>
                <span class="block">{{ defaultTemplateLabel(activePanel) }}</span>
              </span>
            </button>

            <button
              v-for="template in variantTemplates(activePanel)"
              :key="template.template"
              type="button"
              class="flex min-h-10 w-full items-center gap-3 rounded-md px-3 text-left text-sm hover:bg-gray-100"
              @click="onSelectTemplate(template)"
            >
              <span v-html="template.icon"></span>
              {{ template.label }}
            </button>

            <button
              type="button"
              class="mt-1 flex h-10 w-full items-center gap-3 rounded-md px-3 text-left text-sm text-blue-600 hover:bg-blue-50"
              @click="openCreate(activePanel)"
            >
              <i-heroicons-plus-circle class="w-4" />
              {{ t('Create template') }}
            </button>
          </template>
        </Popover.Content>
      </Popover.Positioner>
    </Popover.Root>
  </div>
</template>
