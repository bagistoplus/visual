import NProgress from 'nprogress';

import { useState } from '../state';
import type { Template } from '../types';
import { removeUrlParam, updateUrlParam } from '../utils/urlState';
import { useCraftileEditor } from './useCraftileEditor';

export function isCustomTemplateVariant(template: Template) {
  return Boolean(template.type && template.template !== template.type);
}

export function useTemplateSelection() {
  const editor = useCraftileEditor();
  const { channel, locale, state, theme } = useState();

  function selectTemplate(template: Template) {
    if (!editor) {
      return false;
    }

    if (state.pageData?.template !== template.template) {
      if (state.pageData) {
        state.pageData.template = template.template;
      }

      editor.engine.setPage({
        regions: [],
        blocks: {},
      });
    }

    updateUrlParam('template', template.template);
    removeUrlParam('block');

    NProgress.start();

    const url = new URL(template.previewUrl);
    url.searchParams.set('_designMode', theme.value!.code as string);
    url.searchParams.set('channel', channel.value);
    url.searchParams.set('locale', locale.value);

    if (isCustomTemplateVariant(template)) {
      url.searchParams.set('_template', template.template);
    } else {
      url.searchParams.delete('_template');
    }

    editor.preview.loadUrl(url.href);

    return true;
  }

  return {
    isCustomTemplateVariant,
    selectTemplate,
  };
}
