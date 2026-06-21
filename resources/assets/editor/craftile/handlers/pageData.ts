import type { CraftileEditor } from '@craftile/editor';
import NProgress from 'nprogress';

import type { State } from '../../state';
import { populatePreloadedModels } from '../../state';
import type { PreviewPageData } from '../../types';
import { getUrlParam, removeUrlParam } from '../../utils/urlState';

export function syncEditorBlockSchemas(editor: CraftileEditor, blockSchemas: any[]) {
  const blocksManager = editor.engine.getBlocksManager();

  blockSchemas.forEach((schema) => {
    if (blocksManager.has(schema.type)) {
      blocksManager.unregister(schema.type);
    }

    blocksManager.register(schema.type, schema);
  });
}

export function syncEditorContextFromPageData(state: State, pageData: Partial<PreviewPageData>) {
  if (pageData.channel) {
    state.channel = pageData.channel;
  }

  if (pageData.locale) {
    state.locale = pageData.locale;
  }

  state.localeInheritance = pageData.localeInheritance ?? {};
}

export function setupPageDataHandler(editor: CraftileEditor, state: State) {
  editor.preview.onReady(() => {
    editor.preview.onMessage('craftile.preview.page-data', (data) => {
      const pageData = data.pageData as unknown as PreviewPageData;

      NProgress.done();

      syncEditorContextFromPageData(state, pageData);

      if (pageData.blockSchemas) {
        syncEditorBlockSchemas(editor, pageData.blockSchemas);
      }

      editor.engine.setPage(pageData.content);
      state.previewLoading = false;

      state.pageData = {
        url: pageData.template.url,
        template: pageData.template.name,
        sources: pageData.template.sources,
        channel: pageData.channel,
        locale: pageData.locale,
        settings: pageData.settings,
      };

      if (state.theme && pageData.settings) {
        state.theme.settings = pageData.settings;
      }

      if (pageData.preloadedModels) {
        populatePreloadedModels(pageData.preloadedModels);
      }

      const blockIdToRestore = getUrlParam('block');

      if (blockIdToRestore) {
        const block = editor.engine.getBlockById(blockIdToRestore);
        if (block) {
          editor.ui.setSelectedBlock(blockIdToRestore);
        } else {
          removeUrlParam('block');
        }
      }
    });
  });
}
