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

/**
 * How long the selection is held before it is let go of anyway, so a reload that
 * never lands cannot leave the merchant unable to deselect.
 */
export const HOLD_TIMEOUT = 15000;

export function createBagistoPreviewRefresh(editor: CraftileEditor, state?: State) {
  let pending: PendingRefresh | null = null;

  /**
   * The editor drops its selection whenever the preview frame loads, which is
   * every edit here. On a screen wide enough to keep the settings panel beside
   * the preview, that panel is drawn only while something is selected, so it
   * would close and open again on every keystroke, resizing the preview with
   * it. The selection is held across a reload this feature asked for, and let
   * go of once the page it asked for has arrived.
   */
  let holding = false;
  let holdTimer: ReturnType<typeof setTimeout> | undefined;

  const clearSelectedBlock = editor.ui.clearSelectedBlock.bind(editor.ui);

  editor.ui.clearSelectedBlock = () => {
    if (!holding) {
      clearSelectedBlock();
    }
  };

  function hold() {
    holding = true;

    if (state) {
      state.quietReload = true;
    }

    clearTimeout(holdTimer);
    holdTimer = setTimeout(release, HOLD_TIMEOUT);
  }

  function release() {
    holding = false;

    if (state) {
      state.quietReload = false;
    }

    clearTimeout(holdTimer);
  }

  editor.preview.onReady(() => {
    editor.preview.onMessage('craftile.preview.page-data' as any, () => {
      release();

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
      } else if (refresh.selected) {
        // What was selected is gone from the page that came back, so the panel
        // beside it has nothing left to draw.
        editor.ui.clearSelectedBlock();
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

      hold();
      editor.preview.reload();
    },
  };
}
