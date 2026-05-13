<script setup lang="ts">
import { Button } from '@craftile/editor/ui';

import { createTemplate } from '../api';
import { useState } from '../state';
import type { Template } from '../types';
import useI18n from '../composables/i18n';
import { useTemplateSelection } from '../composables/useTemplateSelection';

const editor = useCraftileEditor()!;
const { t } = useI18n();
const { state, templates } = useState();
const { selectTemplate } = useTemplateSelection();

const templateForm = state.templateForm;

const variantTypes = ['product', 'category', 'page'] as const;

function variantTemplates(type: typeof templateForm.type) {
  return templates.value.filter((template) => template.type === type && template.template !== type);
}

function defaultTemplate(type: typeof templateForm.type) {
  return templates.value.find((template) => template.template === type);
}

function defaultBaseTemplate(type: typeof templateForm.type) {
  const template = defaultTemplate(type);

  return template?.isJsonTemplate ? template : null;
}

function defaultTemplateLabel(type: typeof templateForm.type) {
  return variantTypes.includes(type) ? t(`Default ${type}`) : `Default ${type}`;
}

function updateName(value: string) {
  templateForm.name = value.slice(0, 25);
}

async function submitCreate() {
  if (templateForm.isSubmitting) {
    return;
  }

  templateForm.error = '';

  if (!templateForm.name.trim()) {
    templateForm.error = t('Name is required.');

    return;
  }

  templateForm.isSubmitting = true;

  try {
    const response = await createTemplate({
      type: templateForm.type,
      name: templateForm.name.trim(),
      basedOn: templateForm.basedOn,
    }).execute(undefined, true);

    if (Array.isArray((response as any).templates)) {
      templates.value = (response as any).templates;
    }

    const key = (response as any).template?.key;
    const created = templates.value.find((template: Template) => template.template === key);

    if (created) {
      editor.ui.closeModal('create-template');
      selectTemplate(created);
    }
  } catch (error: any) {
    templateForm.error = error.message || t('Template could not be created.');
  } finally {
    templateForm.isSubmitting = false;
  }
}
</script>

<template>
  <div class="space-y-4 p-4 text-sm">
    <p class="text-gray-700">
      {{ t('create_template_description') }}
    </p>

    <label class="block">
      <span class="mb-1 block text-gray-700">{{ t('Name') }}</span>
      <input
        class="h-9 w-full rounded border px-3 outline-none focus:border-blue-500"
        :value="templateForm.name"
        maxlength="25"
        @input="updateName(($event.target as HTMLInputElement).value)"
      />
      <span class="mt-1 block text-right text-xs text-gray-500">{{ templateForm.name.length }}/25</span>
    </label>

    <label class="block">
      <span class="mb-1 block text-gray-700">{{ t('Based on') }}</span>
      <select
        v-model="templateForm.basedOn"
        class="h-9 w-full rounded border px-3 outline-none focus:border-blue-500"
      >
        <option
          v-if="defaultBaseTemplate(templateForm.type)"
          :value="templateForm.type"
        >
          {{ defaultTemplateLabel(templateForm.type) }}
        </option>
        <option
          v-for="template in variantTemplates(templateForm.type)"
          :key="template.template"
          :value="template.template"
        >
          {{ template.label }}
        </option>
        <option value="__empty__">{{ t('Empty template') }}</option>
      </select>
    </label>

    <p
      v-if="templateForm.error"
      class="text-sm text-red-600"
    >
      {{ templateForm.error }}
    </p>

    <div class="flex justify-end gap-2 border-t pt-4 px-4 -mx-4">
      <Button
        :disabled="templateForm.isSubmitting"
        @click="editor.ui.closeModal('create-template')"
      >
        {{ t('Cancel') }}
      </Button>

      <Button
        variant="primary"
        :loading="templateForm.isSubmitting"
        :disabled="templateForm.isSubmitting || !templateForm.name.trim()"
        @click="submitCreate"
      >
        {{ t('Create template') }}
      </Button>
    </div>
  </div>
</template>
