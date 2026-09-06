import type { CraftileEditor } from '@craftile/editor';

import type { State, UnsupportedPageReason } from '../../state';

export const UNSUPPORTED_PAGE_MODAL = 'unsupported-page';
export const PAGE_DATA_TIMEOUT = 1500;

const WRAPPED_REGISTER_FRAME = Symbol('visual.unsupported-page-wrapped');

export function isTemplateRegistered(state: State, templateName: string | null | undefined): boolean {
  if (!templateName) {
    return false;
  }

  return state.templates.some((template) => template.template === templateName);
}

export function openUnsupportedPageModal(editor: CraftileEditor, state: State, reason: UnsupportedPageReason) {
  state.unsupportedPage = reason;
  editor.ui.openModal(UNSUPPORTED_PAGE_MODAL);
}

export function markPageLoadFailed(editor: CraftileEditor, state: State) {
  editor.engine.setPage({ regions: [], blocks: {} });
  state.previewLoading = false;
  state.pageData = null;

  openUnsupportedPageModal(editor, state, 'load-failed');
}

export function setupUnsupportedPage(editor: CraftileEditor, state: State) {
  const preview = editor.preview as any;

  if (preview[WRAPPED_REGISTER_FRAME]) {
    return;
  }

  let timer: ReturnType<typeof setTimeout> | null = null;
  let boundFrame: HTMLIFrameElement | null = null;

  const onFrameLoad = () => {
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }

    if (!state.previewLoading) {
      return;
    }

    timer = setTimeout(() => {
      timer = null;

      if (state.previewLoading) {
        markPageLoadFailed(editor, state);
      }
    }, PAGE_DATA_TIMEOUT);
  };

  const bindFrame = (frame: HTMLIFrameElement | null) => {
    if (!frame || frame === boundFrame) {
      return;
    }

    boundFrame?.removeEventListener('load', onFrameLoad);
    frame.addEventListener('load', onFrameLoad);
    boundFrame = frame;
  };

  const registerFrame = preview._registerFrame.bind(preview);

  preview._registerFrame = (frame: HTMLIFrameElement) => {
    registerFrame(frame);
    bindFrame(frame);
  };

  preview[WRAPPED_REGISTER_FRAME] = true;
  bindFrame(editor.preview.getFrame());
}
