import { discardTheme } from '../api';
import useI18n from './i18n';
import { useState } from '../state';

const isDiscarding = ref(false);

export function useDiscard() {
  const { t } = useI18n();
  const editor = useCraftileEditor()!;
  const { state } = useState();

  async function discard() {
    if (isDiscarding.value) {
      return;
    }

    isDiscarding.value = true;

    const request = discardTheme();

    request.onSuccess(() => {
      editor.ui.closeModal('confirm-discard');

      state.haveEdits = false;
      state.draftCount = 0;

      // The editor is holding the page it just discarded, and the storefront
      // now draws the published one. Reloading brings both back in step.
      editor.preview.reload();
    });

    request.onError(() => {
      editor.ui.toast({
        type: 'error',
        title: t('Failed to discard edits'),
      });
    });

    await request.execute();

    isDiscarding.value = false;
  }

  return {
    isDiscarding: readonly(isDiscarding),
    discard,
  };
}
