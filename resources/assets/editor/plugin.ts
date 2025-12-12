import type { EngineEvents } from '@craftile/core';
import { CraftileEditorPlugin, PluginContext } from '@craftile/editor';
import { UpdatesEvent } from '@craftile/types';
import { debounce } from 'perfect-debounce';
import NProgress from 'nprogress';

import { useState, populatePreloadedModels } from './state';
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
import GradientPicker from './components/GradientPicker.vue';
import PublishAction from './components/PublishAction.vue';
import PreviewAction from './components/PreviewAction.vue';
import useI18n from './composables/i18n';
import ConfirmPublish from './components/ConfirmPublish.vue';
import BackButton from './components/BackButton.vue';

const { t } = useI18n();
function configureHeader(ui: PluginContext['editor']['ui']) {
  ui.removeHeaderAction('back-button');
  ui.removeHeaderAction('title');

  ui.registerHeaderAction({
    id: 'back-button',
    slot: 'left',
    render: BackButton,
  });

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
    id: 'preview',
    slot: 'right',
    render: PreviewAction,
  });

  ui.registerHeaderAction({
    id: 'publish',
    slot: 'right',
    render: PublishAction,
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

  ui.registerModal({
    id: 'confirm-publish',
    title: t('Publish edits ?'),
    size: 'lg',
    render: ConfirmPublish,
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

  ui.registerPropertyField({
    type: 'gradient',
    render: GradientPicker,
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

    if (update.regions && update.regions.length > 0) {
      merged.regions = update.regions;
    }
  }

  merged.changes.added = [...new Set(merged.changes.added)];
  merged.changes.updated = [...new Set(merged.changes.updated)];
  merged.changes.removed = [...new Set(merged.changes.removed)];

  // Remove blocks from added/updated if they're in removed
  merged.changes.added = merged.changes.added.filter((id) => !merged.changes.removed.includes(id));
  merged.changes.updated = merged.changes.updated.filter((id) => !merged.changes.removed.includes(id));

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

function determineBlocksToProcess(updatedBlocks: Record<string, any>, allBlocks: Record<string, any>): string[] {
  const blocksToProcess: string[] = [];

  for (const [blockId, block] of Object.entries(updatedBlocks)) {
    // Skip if parent is also being updated
    if (block.parentId && updatedBlocks[block.parentId]) {
      continue;
    }

    const repeatedAncestor = findRepeatedAncestor(blockId, allBlocks);
    if (repeatedAncestor) {
      const parentOfRepeated = allBlocks[repeatedAncestor]?.parentId;
      if (parentOfRepeated) blocksToProcess.push(parentOfRepeated);
      continue;
    }

    if (allBlocks[blockId]?.ghost === true) {
      const parentOfGhost = allBlocks[blockId]?.parentId;
      if (parentOfGhost) blocksToProcess.push(parentOfGhost);
      continue;
    }

    blocksToProcess.push(blockId);
  }

  return Array.from(new Set(blocksToProcess));
}

function findRepeatedAncestor(blockId: string, allBlocks: Record<string, any>): string | null {
  let currentId = blockId;
  while (allBlocks[currentId]?.parentId) {
    const parentId = allBlocks[currentId].parentId;
    if (!allBlocks[parentId]) break;
    if (allBlocks[parentId]?.repeated === true) return parentId;
    currentId = parentId;
  }
  return null;
}

function computeEffects(html: string, blocksToUpdate: string[]) {
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, 'text/html');

  const effects: { html: Record<string, string>; css: string[]; js: string[] } = {
    html: {},
    css: [],
    js: [],
  };

  doc.head.querySelectorAll('link[rel="stylesheet"]').forEach((link) => {
    effects.css.push(link.outerHTML);
  });

  doc.querySelectorAll('style').forEach((style) => {
    effects.css.push(style.outerHTML);
  });

  doc.querySelectorAll('script').forEach((script) => {
    effects.js.push(script.outerHTML);
  });

  // Extract block HTML
  for (const blockId of blocksToUpdate) {
    const blockEl = doc.querySelector(`[data-block="${blockId}"]`);
    if (blockEl) {
      effects.html[blockId] = blockEl.outerHTML;
    }
  }

  return effects;
}

export const CRAFTILE_EDITOR = Symbol('CRAFTILE_EDITOR');

export default function (editorConfig: ThemeEditorConfig): CraftileEditorPlugin {
  return ({ vueApp, editor }) => {
    vueApp.provide(CRAFTILE_EDITOR, editor);

    const { state } = useState();

    configureHeader(editor.ui);
    registerPropertyFields(editor.ui);

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
      });
    });

    let pendingUpdates: UpdatesEvent[] = [];
    let isPersisting = false;

    const debouncedPersist = debounce(async () => {
      if (pendingUpdates.length === 0 || isPersisting) {
        return;
      }

      // Take a snapshot of pending updates and clear the queue
      const updatesToProcess = [...pendingUpdates];
      pendingUpdates = [];
      isPersisting = true;

      const mergedUpdates = mergeUpdates(updatesToProcess);

      const request = persistUpdates(mergedUpdates);

      try {
        const htmlResponse = await request.execute();

        const allBlocks = editor.engine.getPage().blocks;
        const blocksToUpdate = determineBlocksToProcess(mergedUpdates.blocks, allBlocks);

        const effects = computeEffects(htmlResponse as string, blocksToUpdate);

        editor.preview.sendMessage('updates.effects', {
          effects,
          ...mergedUpdates,
        });
      } catch (error) {
        if (error instanceof Error && error.name === 'AbortError') {
          return;
        }

        console.error('Failed to persist changes', error);
        editor.ui.toast({
          type: 'error',
          title: 'Failed to save changes',
        });

        // Re-add failed updates back to queue
        pendingUpdates.unshift(...updatesToProcess);
      } finally {
        isPersisting = false;

        // Process any updates that came in while we were persisting
        if (pendingUpdates.length > 0) {
          debouncedPersist();
        }
      }
    }, 300);

    function handleUpdates(updates: UpdatesEvent) {
      if (!hasChanges(updates)) {
        return;
      }

      [...updates.changes.added, ...updates.changes.updated].forEach((id) => {
        const block = updates.blocks[id];
        if (block.ghost && block.parentId && !updates.blocks[block.parentId]) {
          updates.blocks[block.parentId] = editor.engine.getBlockById(block.parentId)!;
        }
      });

      pendingUpdates.push(updates);

      state.haveEdits = true;

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
