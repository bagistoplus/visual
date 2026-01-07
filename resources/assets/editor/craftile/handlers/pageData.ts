import type { CraftileEditor } from '@craftile/editor';
import NProgress from 'nprogress';

import type { State } from '../../state';
import { populatePreloadedModels } from '../../state';
import { getUrlParam, removeUrlParam } from '../../utils/urlState';

export function setupPageDataHandler(editor: CraftileEditor, state: State) {
  editor.preview.onReady(() => {
    editor.preview.onMessage('craftile.preview.page-data', ({ pageData }: any) => {
      NProgress.done();

      editor.engine.setPage(pageData.content);

      state.pageData = {
        url: pageData.template.url,
        template: pageData.template.name,
        sources: pageData.template.sources,
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
