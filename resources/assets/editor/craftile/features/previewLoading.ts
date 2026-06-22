import type { CraftileEditor } from '@craftile/editor';
import { createApp } from 'vue';

import BlocksTreeLoadingOverlay from '../../components/BlocksTreeLoadingOverlay.vue';
import type { State } from '../../state';

const WRAPPED_PREVIEW_LOADING = Symbol('visual.preview-loading-wrapped');

export function setupPreviewLoading(editor: CraftileEditor, state: State) {
  const preview = editor.preview as any;

  if (preview[WRAPPED_PREVIEW_LOADING]) {
    return;
  }

  const loadUrl = editor.preview.loadUrl.bind(editor.preview);
  const reload = editor.preview.reload.bind(editor.preview);
  const startLoading = () => {
    state.previewLoading = true;
    state.localeInheritance = {};
  };

  preview.loadUrl = (url: string) => {
    startLoading();

    return loadUrl(url);
  };

  preview.reload = () => {
    startLoading();

    return reload();
  };

  preview[WRAPPED_PREVIEW_LOADING] = true;
  preview.onDocumentReady(() => bindPreviewFrameNavigationLoading(editor, startLoading));
  mountBlocksTreeLoadingOverlay(editor);
}

function bindPreviewFrameNavigationLoading(editor: CraftileEditor, startLoading: () => void) {
  const frame = editor.preview.getFrame()!;

  frame.contentWindow!.addEventListener('pagehide', startLoading);
  frame.contentWindow!.addEventListener('beforeunload', startLoading);
}

function mountBlocksTreeLoadingOverlay(editor: CraftileEditor) {
  const container = document.createElement('div');
  container.dataset.visualBlocksTreeLoadingOverlay = 'true';
  container.style.position = 'fixed';
  container.style.top = '56px';
  container.style.left = '56px';
  container.style.width = '300px';
  container.style.bottom = '0';
  container.style.zIndex = '1100';
  container.style.pointerEvents = 'none';
  document.body.appendChild(container);

  createApp(BlocksTreeLoadingOverlay, { editor }).mount(container);
}
