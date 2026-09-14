import type { CraftileEditor } from '@craftile/editor';
import type { UpdatesEvent } from '@craftile/types';

import type { State } from '../../state';

export const SCROLL_TO_BLOCK_MESSAGE = 'visual.scroll-to-block';
export const SELECT_BLOCK_MESSAGE = 'craftile.editor.select-block';

export interface BlockPosition {
  region: string;
  index: number;
}

export interface PendingRefresh {
  blockId: string;
  position: BlockPosition | null;
  selected: string | null;
}

/**
 * A theme whose sections Bagisto draws carries none of the attributes a live
 * update needs, in markup Visual has no say over. The saved page is the only
 * accurate one, so every edit reloads the preview, brings the section that was
 * edited back into view, and puts back whatever the merchant had selected.
 */
export function isBagistoTheme(state: State): boolean {
  return state.theme?.editorMode === 'bagisto';
}

/**
 * The section an edited block belongs to: only sections are rendered with an
 * element the preview can scroll to, so an edited entry stands for the section
 * holding it.
 */
export function rootBlockOf(blockId: string, blocks: Record<string, any>): string {
  const seen = new Set<string>();
  let currentId = blockId;

  while (!seen.has(currentId)) {
    seen.add(currentId);

    const parentId = blocks[currentId]?.parentId;

    if (!parentId || !blocks[parentId]) {
      return currentId;
    }

    currentId = parentId;
  }

  return currentId;
}

export function editedBlockId(updates: UpdatesEvent): string | null {
  const changed = [
    ...(updates.changes.added || []),
    ...(updates.changes.updated || []),
    ...Object.keys(updates.changes.moved || {}),
  ];

  return changed[0] ?? null;
}

/**
 * Where a block sits, rather than what it is called. Saving a new section gives
 * it the id of the row it was stored as, so the id the editor knew it by is
 * gone by the time the reloaded page arrives.
 */
export function locateBlock(page: any, blockId: string): BlockPosition | null {
  for (const region of page?.regions || []) {
    const index = (region.blocks || []).indexOf(blockId);

    if (index !== -1) {
      return { region: region.name, index };
    }
  }

  return null;
}

export function blockAtPosition(page: any, position: BlockPosition | null): string | null {
  if (!position) {
    return null;
  }

  const region = (page?.regions || []).find((region: any) => region.name === position.region);

  return region?.blocks?.[position.index] ?? null;
}

/**
 * The block to select once the page has been reloaded: the one that was already
 * selected, under whatever id it now goes by. Selecting anything else would move
 * the settings panel away from what the merchant is editing.
 */
export function selectionAfterReload(page: any, pending: PendingRefresh, scrollTo: string | null): string | null {
  if (!pending.selected) {
    return null;
  }

  if (page?.blocks?.[pending.selected]) {
    return pending.selected;
  }

  return pending.selected === pending.blockId ? scrollTo : null;
}

export function createBagistoPreviewRefresh(editor: CraftileEditor) {
  let pending: PendingRefresh | null = null;

  editor.preview.onReady(() => {
    editor.preview.onMessage('craftile.preview.page-data' as any, () => {
      if (!pending) {
        return;
      }

      const refresh = pending;
      pending = null;

      const page = editor.engine.getPage();
      const scrollTo = blockAtPosition(page, refresh.position);
      const selection = selectionAfterReload(page, refresh, scrollTo);

      if (selection) {
        editor.ui.setSelectedBlock(selection);
        editor.preview.sendMessage(SELECT_BLOCK_MESSAGE as any, { blockId: selection });
      }

      if (scrollTo) {
        editor.preview.sendMessage(SCROLL_TO_BLOCK_MESSAGE as any, { blockId: scrollTo });
      }
    });
  });

  return {
    refresh(updates: UpdatesEvent) {
      const page = editor.engine.getPage();
      const blockId = editedBlockId(updates);
      const rootId = blockId ? rootBlockOf(blockId, page.blocks) : null;

      pending = rootId
        ? {
            blockId: rootId,
            position: locateBlock(page, rootId),
            selected: editor.ui.state.selectedBlockId ?? null,
          }
        : null;

      editor.preview.reload();
    },
  };
}
