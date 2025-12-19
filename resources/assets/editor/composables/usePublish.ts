import { publishTheme } from '../api';
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

    const pageData = editor.engine.getPage();

    const request = publishTheme(pageData);

    request.onSuccess(() => {
      editor.ui.toast({
        type: 'success',
        title: 'Theme published successfully',
      });
      editor.ui.closeModal('confirm-publish');

      state.haveEdits = false;
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
