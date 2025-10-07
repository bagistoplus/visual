import { Block, BlockSchema } from '@craftile/types';
import { createCraftileEditor } from '@craftile/editor';
import CommonPropertiesPlugin from '@craftile/plugin-common-properties';
import VisualPlugin from './plugin';
import './css/index.css';
import * as Vue from 'vue';
import { createState } from './state';

const { blockSchemas } = window.editorConfig;

// Create state before plugin initialization
const state = createState({
  channels: window.editorConfig.channels,
  channel: window.editorConfig.defaultChannel,
  theme: window.editorConfig.theme,
});

const editorInstance = createCraftileEditor({
  el: '#app',
  blockSchemas,
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
    if (!context.parentId) {
      // we are adding a root block to a region, so we only allow section
      return schema.meta?.is_section;
    }

    return !schema.meta?.is_section;
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
