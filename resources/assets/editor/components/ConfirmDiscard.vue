<script setup lang="ts">
import { Button } from '@craftile/editor/ui';
import useI18n from '../composables/i18n';
import { useDiscard } from '../composables/useDiscard';

const { t } = useI18n();
const editor = useCraftileEditor()!;
const { isDiscarding, discard } = useDiscard();
</script>

<template>
  <div class="w-sm p-4 space-y-6">
    <p>{{ t('discard_warning_line1') }}</p>
    <p>{{ t('discard_warning_line2') }}</p>

    <div class="flex gap-4 justify-end">
      <Button
        :disabled="isDiscarding"
        @click="editor.ui.closeModal('confirm-discard')"
      >
        {{ t('Cancel') }}
      </Button>
      <Button
        variant="destructive"
        :loading="isDiscarding"
        @click="discard"
      >
        {{ t('Discard') }}
      </Button>
    </div>
  </div>
</template>
