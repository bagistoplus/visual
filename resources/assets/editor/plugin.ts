import type { EngineEvents } from '@craftile/core';
import { CraftileEditorPlugin, PluginContext } from '@craftile/editor';
import { UpdatesEvent } from '@craftile/types';
import { debounce } from 'perfect-debounce';

import { useState } from './state';
import { persistUpdates } from './api';
import { ThemeEditorConfig } from './types';

import HeroiconsCog6Tooth from '~icons/heroicons/cog-6-tooth';
import HeroiconsPhoto from '~icons/heroicons/photo';
import HeaderTitle from './components/HeaderTitle.vue';
import HeaderTools from './components/HeaderTools.vue';
import ThemeSettingsPanel from './components/ThemeSettingsPanel.vue';
import MediaPanel from './components/MediaPanel.vue';
import CategoryPicker from './components/CategoryPicker.vue';
import ProductPicker from './components/ProductPicker.vue';
import CmsPagePicker from './components/CmsPagePicker.vue';
import FontPicker from './components/FontPicker.vue';
import LinkPicker from './components/LinkPicker.vue';
import ColorSchemePicker from './components/ColorSchemePicker.vue';
import ColorSchemeGroup from './components/ColorSchemeGroup.vue';
import IconPicker from './components/IconPicker.vue';
import ImagePicker from './components/ImagePicker.vue';
import RichtextEditor from './components/RichtextEditor.vue';

function configureHeader(ui: PluginContext['editor']['ui']) {
  ui.removeHeaderAction('title');

  ui.registerHeaderAction({
    id: 'title',
    slot: 'left',
    render: HeaderTitle,
  });

  ui.registerHeaderAction({
    id: 'tools',
    slot: 'center',
    render: HeaderTools,
  });

  ui.registerHeaderAction({
    id: 'publish',
    slot: 'right',
    button: {
      variant: 'primary',
      text: 'Publish',
      onClick(_e, { toggleLoading }) {
        toggleLoading();
        setTimeout(toggleLoading, 5000);
      },
    },
  });

  ui.registerSidebarPanel({
    id: 'theme-settings',
    title: 'Theme settings',
    icon: HeroiconsCog6Tooth,
    render: ThemeSettingsPanel,
  });

  ui.registerSidebarPanel({
    id: 'media',
    title: 'Medias',
    icon: HeroiconsPhoto,
    render: MediaPanel,
  });
}

function registerPropertyFields(ui: PluginContext['editor']['ui']) {
  ui.registerPropertyField({
    type: 'category',
    render: CategoryPicker,
  });

  ui.registerPropertyField({
    type: 'product',
    render: ProductPicker,
  });

  ui.registerPropertyField({
    type: 'cms-page',
    render: CmsPagePicker,
  });

  ui.registerPropertyField({
    type: 'font',
    render: FontPicker,
  });

  ui.registerPropertyField({
    type: 'link',
    render: LinkPicker,
  });

  ui.registerPropertyField({
    type: 'color-scheme',
    render: ColorSchemePicker,
  });

  ui.registerPropertyField({
    type: 'color-scheme-group',
    render: ColorSchemeGroup,
  });

  ui.registerPropertyField({
    type: 'icon',
    render: IconPicker,
  });

  ui.registerPropertyField({
    type: 'image',
    render: ImagePicker,
  });

  ui.registerPropertyField({
    type: 'richtext',
    render: RichtextEditor,
  });
}

function mergeUpdates(updates: UpdatesEvent[]): UpdatesEvent {
  const merged: UpdatesEvent = {
    changes: {
      added: [],
      updated: [],
      removed: [],
      moved: {},
    },
    blocks: {},
    regions: [],
  };

  for (const update of updates) {
    merged.changes.added.push(...update.changes.added);
    merged.changes.updated.push(...update.changes.updated);
    merged.changes.removed.push(...update.changes.removed);
    Object.assign(merged.changes.moved, update.changes.moved || {});
    Object.assign(merged.blocks, update.blocks);
    merged.regions = update.regions;
  }

  merged.changes.added = [...new Set(merged.changes.added)];
  merged.changes.updated = [...new Set(merged.changes.updated)];
  merged.changes.removed = [...new Set(merged.changes.removed)];

  return merged;
}

function hasChanges(updates: UpdatesEvent): boolean {
  return (
    updates.changes.added.length > 0 ||
    updates.changes.updated.length > 0 ||
    updates.changes.removed.length > 0 ||
    Object.keys(updates.changes.moved || {}).length > 0
  );
}

export const CRAFTILE_EDITOR = Symbol('CRAFTILE_EDITOR');

export default function (editorConfig: ThemeEditorConfig): CraftileEditorPlugin {
  return ({ vueApp, editor }) => {
    // State and HTTP client are now created in index.ts before plugin initialization
    vueApp.provide(CRAFTILE_EDITOR, editor);

    const { state } = useState();

    configureHeader(editor.ui);
    registerPropertyFields(editor.ui);

    editor.preview.onReady(() => {
      editor.preview.onMessage('craftile.preview.page-data', ({ pageData }: any) => {
        editor.engine.setPage(pageData.content);

        state.pageData = {
          url: pageData.template.url,
          template: pageData.template.name,
          sources: pageData.template.sources,
        };
      });
    });

    let pendingUpdates: UpdatesEvent[] = [];
    const debouncedPersist = debounce(async () => {
      if (pendingUpdates.length === 0) {
        return;
      }

      const mergedUpdates = mergeUpdates(pendingUpdates);

      const request = persistUpdates(mergedUpdates);

      try {
        const result = await request.execute();

        pendingUpdates = [];

        if (result && result.effects) {
          editor.preview.sendMessage('updates.effects', {
            effects: result.effects,
            ...mergedUpdates,
          });
        }
      } catch (error) {
        if (error instanceof Error && error.name === 'AbortError') {
          return;
        }

        console.error('Failed to persist changes', error);
        editor.ui.toast({
          type: 'error',
          title: 'Failed to save changes',
        });
      } finally {
        if (pendingUpdates.length > 0) {
          debouncedPersist();
        }
      }
    }, 300);

    function handleUpdates(updates: UpdatesEvent) {
      if (!hasChanges(updates)) {
        return;
      }

      pendingUpdates.push(updates);
      debouncedPersist();
    }

    function handlePropertyUpdate(payload: EngineEvents['block:property:set']) {
      const { blockId, key, value, oldValue } = payload;
      const block = editor.engine.getBlockById(blockId);
      editor.preview.sendMessage('block.property.updated' as any, { block, key, value, oldValue });
    }

    editor.engine.on('block:property:set', handlePropertyUpdate);
    editor.events.on('updates', handleUpdates);
    editor.preview.loadUrl(editorConfig.storefrontUrl);
  };
}
