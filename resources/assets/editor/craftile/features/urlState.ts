import type { CraftileEditor } from '@craftile/editor';

import type { State } from '../../state';
import type { ThemeEditorConfig } from '../../types';
import { updateUrlParam, removeUrlParam, getUrlParam } from '../../utils/urlState';

export function setupBlockPersistence(editor: CraftileEditor) {
  editor.events.on('ui:block:select', ({ blockId }: { blockId: string }) => {
    updateUrlParam('block', blockId);
  });

  editor.events.on('ui:block:clear-selection', () => {
    removeUrlParam('block');
  });
}

export function loadTemplateFromUrl(editor: CraftileEditor, state: State, editorConfig: ThemeEditorConfig) {
  const urlTemplate = getUrlParam('template');
  const template = urlTemplate && editorConfig.templates?.find((t) => t.template === urlTemplate);

  if (template) {
    const url = new URL(template.previewUrl);
    url.searchParams.set('_designMode', state.theme?.code as string);
    url.searchParams.set('channel', state.channel);
    url.searchParams.set('locale', state.locale);
    editor.preview.loadUrl(url.href);
  } else {
    editor.preview.loadUrl(editorConfig.storefrontUrl);
  }
}

export function setupUrlState(editor: CraftileEditor, state: State, editorConfig: ThemeEditorConfig) {
  setupBlockPersistence(editor);
  loadTemplateFromUrl(editor, state, editorConfig);
}
