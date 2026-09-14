<script setup lang="ts">
import { Button } from '@craftile/editor/ui';
import useI18n from '../composables/i18n';
import { useDiscard } from '../composables/useDiscard';
import { useState } from '../state';

const { t } = useI18n();
const editor = useCraftileEditor()!;
const { isDiscarding } = useDiscard();
const { haveEdits } = useState();
</script>

<template>
  <Button
    :loading="isDiscarding"
    :disabled="!haveEdits"
    @click="editor.ui.openModal('confirm-discard')"
  >
    {{ t('Discard') }}
  </Button>
</template>
