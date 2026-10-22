<script setup lang="ts">
import { Button } from '@craftile/editor/ui';
import useI18n from '../composables/i18n';
import { usePublish } from '../composables/usePublish';
import { useState } from '../state';

const CONFIRM_PUBLISH_KEY = 'bagisto_visual_editor_confirm_publish';

const { t } = useI18n();
const editor = useCraftileEditor()!;
const { isPublishing, publish } = usePublish();
const { haveEdits, draftCount } = useState();

// Bagisto drafts a whole section, so the count says how many sections would go
// live, the way its own appearance editor puts it.
const label = computed(() =>
  draftCount.value ? t('Publish (:count)', { count: draftCount.value }) : t('Publish')
);

function onClick() {
  if (null === localStorage.getItem(CONFIRM_PUBLISH_KEY)) {
    editor.ui.openModal('confirm-publish');
    return;
  }

  publish();
}
</script>

<template>
  <Button
    variant="primary"
    :loading="isPublishing"
    :disabled="!haveEdits"
    @click="onClick"
  >
    {{ label }}
  </Button>
</template>