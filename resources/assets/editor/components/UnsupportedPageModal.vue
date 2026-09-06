<script setup lang="ts">
import { Button } from '@craftile/editor/ui';

import { TEMPLATES_DOCS_URL } from '../constants/docs';
import useI18n from '../composables/i18n';
import { useCraftileEditor } from '../composables/useCraftileEditor';
import { useState } from '../state';
import { isCustomTemplateVariant, useTemplateSelection } from '../composables/useTemplateSelection';
import { UNSUPPORTED_PAGE_MODAL } from '../craftile/features/unsupportedPage';

const editor = useCraftileEditor()!;
const { t } = useI18n();
const { state, templates } = useState();
const { selectTemplate } = useTemplateSelection();

const isLoadFailure = computed(() => state.unsupportedPage === 'load-failed');

const homeTemplate = computed(() => {
  return (
    templates.value.find((template) => template.template === 'index') ||
    templates.value.find((template) => template.template !== '__separator__' && !isCustomTemplateVariant(template)) ||
    null
  );
});

function openDocumentation() {
  window.open(TEMPLATES_DOCS_URL, '_blank');
}

function goToHomePage() {
  if (homeTemplate.value && selectTemplate(homeTemplate.value)) {
    editor.ui.closeModal(UNSUPPORTED_PAGE_MODAL);
  }
}
</script>

<template>
  <div class="space-y-4 p-4 text-sm">
    <p class="text-gray-700">
      {{ t(isLoadFailure ? 'unsupported_page_load_failed' : 'unsupported_page_missing_template') }}
    </p>
    <p class="text-gray-700">{{ t('unsupported_page_hint') }}</p>

    <div class="flex gap-4 justify-end">
      <Button @click="openDocumentation">
        {{ t('View documentation') }}
      </Button>
      <Button
        variant="primary"
        :disabled="!homeTemplate"
        @click="goToHomePage"
      >
        {{ t('Go to home page') }}
      </Button>
    </div>
  </div>
</template>
