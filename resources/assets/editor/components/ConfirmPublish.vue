<script setup lang="ts">
import { Button } from '@craftile/editor/ui';
import useI18n from '../composables/i18n';
import { usePublish } from '../composables/usePublish';

const CONFIRM_PUBLISH_KEY = 'bagisto_visual_editor_confirm_publish';

const { t } = useI18n();
const editor = useCraftileEditor()!;
const { isPublishing, publish } = usePublish();

const dontAskNextTime = ref(false);

function onPublish() {
  if (dontAskNextTime.value) {
    localStorage.setItem(CONFIRM_PUBLISH_KEY, 'true');
  }

  publish();
}
</script>

<template>
  <div class="w-sm p-4 space-y-6">
    <p>{{ t('publish_warning_line1') }}</p>
    <p>{{ t('publish_warning_line2') }}</p>

    <div class=" mb-4">
      <Checkbox
        :label="t('Don\'t ask next time')"
        v-model="dontAskNextTime"
      />
    </div>

    <div class="flex gap-4 justify-end">
      <Button
        :disabled="isPublishing"
        @click="editor.ui.closeModal('confirm-publish')"
      >
        {{ t('Cancel') }}
      </Button>
      <Button
        variant="primary"
        :loading="isPublishing"
        @click="onPublish"
      >
        {{ t('Publish') }}
      </Button>
    </div>
  </div>
</template>