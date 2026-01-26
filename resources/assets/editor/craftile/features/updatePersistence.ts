import type { EngineEvents } from '@craftile/core';
import type { CraftileEditor } from '@craftile/editor';
import type { UpdatesEvent } from '@craftile/types';
import { debounce } from 'perfect-debounce';

import type { State } from '../../state';
import { persistUpdates } from '../../api';
import useI18n from '../../composables/i18n';

const { t } = useI18n();

export function mergeUpdates(updates: UpdatesEvent[]): UpdatesEvent {
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

  merged.changes.added = merged.changes.added.filter((id) => !merged.changes.removed.includes(id));
  merged.changes.updated = merged.changes.updated.filter((id) => !merged.changes.removed.includes(id));

  return merged;
}

export function hasChanges(updates: UpdatesEvent): boolean {
  return (
    updates.changes.added.length > 0 ||
    updates.changes.updated.length > 0 ||
    updates.changes.removed.length > 0 ||
    Object.keys(updates.changes.moved || {}).length > 0
  );
}

export function determineBlocksToProcess(blockIds: string[], allBlocks: Record<string, any>): string[] {
  const blocksToProcess: string[] = [];

  for (const blockId of blockIds) {
    const block = allBlocks[blockId];

    if (!block) {
      continue;
    }

    if (block.parentId && blockIds.includes(block.parentId)) {
      continue;
    }

    const repeatedAncestor = findRepeatedAncestor(blockId, allBlocks);
    if (repeatedAncestor) {
      const parentOfRepeated = allBlocks[repeatedAncestor]?.parentId;
      if (parentOfRepeated) {
        blocksToProcess.push(parentOfRepeated);
      }
      continue;
    }

    if (block.ghost === true) {
      const parentOfGhost = block.parentId;
      if (parentOfGhost) {
        blocksToProcess.push(parentOfGhost);
      }
      continue;
    }

    blocksToProcess.push(blockId);
  }

  return Array.from(new Set(blocksToProcess));
}

export function findRepeatedAncestor(blockId: string, allBlocks: Record<string, any>): string | null {
  let currentId = blockId;

  while (allBlocks[currentId]?.parentId) {
    const parentId = allBlocks[currentId].parentId;

    if (!allBlocks[parentId]) {
      break;
    }

    if (allBlocks[parentId]?.repeated === true) {
      return parentId;
    }

    currentId = parentId;
  }

  return null;
}

export function computeEffects(html: string, blocksToUpdate: string[]) {
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

  for (const blockId of blocksToUpdate) {
    const blockEl = doc.querySelector(`[data-block="${blockId}"]`);
    if (blockEl) {
      effects.html[blockId] = blockEl.outerHTML;
    }
  }

  return effects;
}

export function setupUpdatePersistence(editor: CraftileEditor, state: State) {
  let pendingUpdates: UpdatesEvent[] = [];
  let failedUpdates: UpdatesEvent[] = [];
  let isPersisting = false;

  const debouncedPersist = debounce(async () => {
    if (pendingUpdates.length === 0 || isPersisting) {
      return;
    }

    const updatesToProcess = [...pendingUpdates];

    if (failedUpdates.length > 0) {
      updatesToProcess.unshift(...failedUpdates);
      failedUpdates = [];
    }

    pendingUpdates = [];
    isPersisting = true;

    const mergedUpdates = mergeUpdates(updatesToProcess);

    const request = persistUpdates(mergedUpdates);

    request.onSuccess((htmlResponse) => {
      const allBlocks = editor.engine.getPage().blocks;
      const directlyModifiedIds = [...(mergedUpdates.changes.added || []), ...(mergedUpdates.changes.updated || [])];
      const blocksToUpdate = determineBlocksToProcess(directlyModifiedIds, allBlocks);

      const effects = computeEffects(htmlResponse, blocksToUpdate);

      editor.preview.sendMessage('updates.effects', {
        effects,
        ...mergedUpdates,
      });
    });

    request.onError((error) => {
      if (error.name === 'AbortError') {
        return;
      }

      console.error('Failed to persist changes', error);

      editor.ui.toast({
        type: 'error',
        title: t('Failed to save changes'),
      });

      failedUpdates = updatesToProcess;
    });

    request.onFinish(() => {
      isPersisting = false;

      if (pendingUpdates.length > 0) {
        debouncedPersist();
      }
    });

    await request.execute();
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
}
