import type { CraftileEditorPlugin } from '@craftile/editor';

import type { ThemeEditorConfig } from '../types';
import { useState } from '../state';
import { configureUI } from './config/ui';
import { setupUpdatePersistence } from './features/updatePersistence';
import { setupUrlState } from './features/urlState';
import { setupPageDataHandler } from './handlers/pageData';

export const CRAFTILE_EDITOR = Symbol('CRAFTILE_EDITOR');

export default function (editorConfig: ThemeEditorConfig): CraftileEditorPlugin {
  return ({ vueApp, editor }) => {
    vueApp.provide(CRAFTILE_EDITOR, editor);

    const { state } = useState();

    configureUI(editor.ui);
    setupPageDataHandler(editor, state);
    setupUpdatePersistence(editor, state);
    setupUrlState(editor, state, editorConfig);
  };
}
