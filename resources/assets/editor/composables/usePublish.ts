import { publishTheme } from '../api';
import { flushPendingUpdates } from '../craftile/features/updatePersistence';
import { useState } from '../state';

const isPublishing = ref(false);

export function usePublish() {
  const editor = useCraftileEditor()!;
  const { state } = useState();

  async function publish() {
    if (isPublishing.value) {
      return;
    }

    isPublishing.value = true;

    // Publish what is saved, so edits still on their way are saved first. A
    // failed save has already said so, and publishing would leave it out.
    if (!(await flushPendingUpdates())) {
      isPublishing.value = false;

      return;
    }

    const request = publishTheme();

    request.onSuccess(() => {
      editor.ui.toast({
        type: 'success',
        title: 'Theme published successfully',
      });
      editor.ui.closeModal('confirm-publish');

      state.haveEdits = false;

      if (state.draftCount !== null) {
        state.draftCount = 0;
      }
    });

    request.onError(() => {
      editor.ui.toast({
        type: 'error',
        title: 'Failed to publish theme',
      });
    });

    await request.execute();

    isPublishing.value = false;
  }

  return {
    isPublishing: readonly(isPublishing),
    publish,
  };
}
