import { Block, BlockSchema } from '@craftile/types';
import { createCraftileEditor } from '@craftile/editor';
import CommonPropertiesPlugin from '@craftile/plugin-common-properties';
import VisualPlugin from './plugin';
import './css/index.css';
import * as Vue from 'vue';
import { createState } from './state';
import { matchesCondition } from './utils/patternMatcher';

const { blockSchemas } = window.editorConfig;

const state = createState({
  channels: window.editorConfig.channels,
  channel: window.editorConfig.defaultChannel,
  theme: window.editorConfig.theme,
  templates: window.editorConfig.templates || [],
  haveEdits: window.editorConfig.haveEdits,
});

const editorInstance = createCraftileEditor({
  el: '#app',
  blockSchemas,
  devices: {
    presets: [
      { id: 'mobile', label: 'Mobile', width: 376, icon: 'mobile' },
      { id: 'tablet', label: 'Tablet', width: 767, icon: 'tablet' },
      { id: 'desktop', label: 'Desktop', width: 1280, icon: 'desktop' },
    ],
  },
  i18n: window.editorConfig.messages.craftile,
  plugins: [CommonPropertiesPlugin, VisualPlugin(window.editorConfig)],
  blockLabelFunction(block, schema) {
    let label: string | null = null;

    for (const fn of [getText]) {
      if ((label = fn(block, schema))) {
        break;
      }
    }

    return label ?? '';
  },
  blockFilterFunction(schema, context) {
    const pageContext = {
      template: state.pageData?.template,
      region: context.regionName,
    };

    // Check if block is explicitly disabled (takes precedence)
    if (matchesCondition(schema.meta?.disabledOn || {}, pageContext)) {
      return false;
    }

    const hasEnabledOnConditions =
      (schema.meta?.enabledOn?.regions?.length ?? 0) > 0 || (schema.meta?.enabledOn?.templates?.length ?? 0) > 0;

    if (hasEnabledOnConditions && !matchesCondition(schema.meta!.enabledOn!, pageContext)) {
      return false;
    }

    if (!context.parentId) {
      // we are adding a root block to a region, so we only allow section
      return schema.meta?.isSection;
    }

    return !schema.meta?.isSection;
  },
});

// Expose editor instance and Vue globally for user extensions
window.craftileEditor = editorInstance;
window.Vue = Vue;

document.addEventListener('DOMContentLoaded', () => {
  document.dispatchEvent(
    new CustomEvent('visual:editor:ready', {
      detail: {
        editor: editorInstance,
      },
    })
  );
});

const getText = (block: Block, schema?: BlockSchema) => {
  if (!schema) {
    return null;
  }

  const textProp = schema.properties.find((s) => s.type === 'text');
  if (!textProp) {
    return null;
  }

  const text = block.properties[textProp.id];
  return text ? sanitizeString(text as string) : null;
};

function sanitizeString(input: string) {
  const doc = new DOMParser().parseFromString(input, 'text/html');
  const content = doc.body.textContent;
  return content === 'undefined' || content === 'null' ? input : content;
}
