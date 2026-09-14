import { describe, it, expect, vi } from 'vitest';

import {
  SCROLL_TO_BLOCK_MESSAGE,
  SELECT_BLOCK_MESSAGE,
  blockAtPosition,
  createBagistoPreviewRefresh,
  editedBlockId,
  isBagistoTheme,
  locateBlock,
  rootBlockOf,
  selectionAfterReload,
} from '../../../craftile/features/bagistoPreviewRefresh';

function createEditor(page: any, selectedBlockId: string | null = null) {
  const pageDataHandlers: Array<() => void> = [];
  const sendMessage = vi.fn();
  const reload = vi.fn();
  const setSelectedBlock = vi.fn();

  const editor = {
    engine: {
      getPage: vi.fn(() => page),
    },
    ui: {
      state: { selectedBlockId },
      setSelectedBlock,
    },
    preview: {
      onReady: (callback: () => void) => callback(),
      onMessage: (_message: string, handler: () => void) => pageDataHandlers.push(handler),
      sendMessage,
      reload,
    },
  } as any;

  return {
    editor,
    sendMessage,
    setSelectedBlock,
    reload,
    loadPreview(nextPage?: any) {
      if (nextPage) {
        page = nextPage;
        editor.engine.getPage = vi.fn(() => page);
      }

      pageDataHandlers.forEach((handler) => handler());
    },
  };
}

function createUpdates(changes: { added?: string[]; updated?: string[]; moved?: Record<string, any> }) {
  return {
    changes: {
      added: changes.added || [],
      updated: changes.updated || [],
      removed: [],
      moved: changes.moved || {},
      positions: {},
    },
    blocks: {},
    regions: [],
  } as any;
}

describe('isBagistoTheme', () => {
  it('is true only for a theme bagisto stores the sections of', () => {
    expect(isBagistoTheme({ theme: { editorMode: 'bagisto' } } as any)).toBe(true);
    expect(isBagistoTheme({ theme: { editorMode: 'visual' } } as any)).toBe(false);
    expect(isBagistoTheme({ theme: null } as any)).toBe(false);
  });
});

describe('rootBlockOf', () => {
  const blocks = {
    'section-7': { id: 'section-7' },
    'section-7.images.1': { id: 'section-7.images.1', parentId: 'section-7' },
    'section-7.images.1.links.0': { id: 'section-7.images.1.links.0', parentId: 'section-7.images.1' },
  };

  it('climbs from an entry to the section holding it', () => {
    expect(rootBlockOf('section-7.images.1.links.0', blocks)).toBe('section-7');
  });

  it('leaves a section alone', () => {
    expect(rootBlockOf('section-7', blocks)).toBe('section-7');
  });

  it('stops at a parent the page does not hold', () => {
    expect(rootBlockOf('orphan', { orphan: { id: 'orphan', parentId: 'gone' } })).toBe('orphan');
  });
});

describe('editedBlockId', () => {
  it('prefers an added block, then an updated one, then a moved one', () => {
    expect(editedBlockId(createUpdates({ added: ['fresh'], updated: ['section-7'] }))).toBe('fresh');
    expect(editedBlockId(createUpdates({ updated: ['section-7'] }))).toBe('section-7');
    expect(editedBlockId(createUpdates({ moved: { 'section-8': {} } }))).toBe('section-8');
    expect(editedBlockId(createUpdates({}))).toBeNull();
  });
});

describe('locateBlock', () => {
  const page = {
    regions: [
      { name: 'content', blocks: ['section-7', 'section-8'] },
      { name: 'layout', blocks: ['section-9'] },
    ],
  };

  it('reports the region a block sits in and where', () => {
    expect(locateBlock(page, 'section-8')).toEqual({ region: 'content', index: 1 });
    expect(locateBlock(page, 'section-9')).toEqual({ region: 'layout', index: 0 });
    expect(locateBlock(page, 'nowhere')).toBeNull();
  });

  it('reads a position back', () => {
    expect(blockAtPosition(page, { region: 'content', index: 1 })).toBe('section-8');
    expect(blockAtPosition(page, { region: 'content', index: 9 })).toBeNull();
    expect(blockAtPosition(page, { region: 'missing', index: 0 })).toBeNull();
  });
});

describe('selectionAfterReload', () => {
  const page = { blocks: { 'section-7': {} } };

  it('keeps a selection the reloaded page still holds', () => {
    expect(selectionAfterReload(page, { blockId: 'section-7', position: null, selected: 'section-7' }, null))
      .toBe('section-7');
  });

  it('follows the block that changed to the id it was stored as', () => {
    expect(selectionAfterReload(page, { blockId: 'fresh', position: null, selected: 'fresh' }, 'section-12'))
      .toBe('section-12');
  });

  it('gives up on a selection that is gone and did not change', () => {
    expect(selectionAfterReload(page, { blockId: 'fresh', position: null, selected: 'gone' }, 'section-12'))
      .toBeNull();
    expect(selectionAfterReload(page, { blockId: 'fresh', position: null, selected: null }, 'section-12'))
      .toBeNull();
  });
});

describe('createBagistoPreviewRefresh', () => {
  const page = {
    blocks: {
      'section-7': { id: 'section-7' },
      'section-7.images.1': { id: 'section-7.images.1', parentId: 'section-7' },
    },
    regions: [{ name: 'content', blocks: ['section-7'] }],
  };

  it('reloads the preview and scrolls to the section of the edited entry', () => {
    const { editor, sendMessage, reload, loadPreview } = createEditor(page);

    createBagistoPreviewRefresh(editor).refresh(createUpdates({ updated: ['section-7.images.1'] }));

    expect(reload).toHaveBeenCalledTimes(1);
    expect(sendMessage).not.toHaveBeenCalled();

    loadPreview();

    expect(sendMessage).toHaveBeenCalledWith(SCROLL_TO_BLOCK_MESSAGE, { blockId: 'section-7' });
  });

  it('scrolls to the row an added section was stored as', () => {
    const { editor, sendMessage, loadPreview } = createEditor({
      blocks: { fresh: { id: 'fresh' } },
      regions: [{ name: 'content', blocks: ['section-7', 'fresh'] }],
    });

    createBagistoPreviewRefresh(editor).refresh(createUpdates({ added: ['fresh'] }));

    loadPreview({
      blocks: { 'section-12': { id: 'section-12' } },
      regions: [{ name: 'content', blocks: ['section-7', 'section-12'] }],
    });

    expect(sendMessage).toHaveBeenCalledWith(SCROLL_TO_BLOCK_MESSAGE, { blockId: 'section-12' });
  });

  it('puts the selection back once the page has reloaded', () => {
    const { editor, sendMessage, setSelectedBlock, loadPreview } = createEditor(page, 'section-7');

    createBagistoPreviewRefresh(editor).refresh(createUpdates({ updated: ['section-7'] }));
    loadPreview();

    expect(setSelectedBlock).toHaveBeenCalledWith('section-7');
    expect(sendMessage).toHaveBeenCalledWith(SELECT_BLOCK_MESSAGE, { blockId: 'section-7' });
  });

  it('keeps an entry selected rather than selecting the section it scrolls to', () => {
    const { editor, sendMessage, setSelectedBlock, loadPreview } = createEditor(page, 'section-7.images.1');

    createBagistoPreviewRefresh(editor).refresh(createUpdates({ updated: ['section-7.images.1'] }));
    loadPreview();

    expect(setSelectedBlock).toHaveBeenCalledWith('section-7.images.1');
    expect(sendMessage).toHaveBeenCalledWith(SCROLL_TO_BLOCK_MESSAGE, { blockId: 'section-7' });
  });

  it('selects nothing when the merchant had selected nothing', () => {
    const { editor, sendMessage, setSelectedBlock, loadPreview } = createEditor(page);

    createBagistoPreviewRefresh(editor).refresh(createUpdates({ updated: ['section-7'] }));
    loadPreview();

    expect(setSelectedBlock).not.toHaveBeenCalled();
    expect(sendMessage).toHaveBeenCalledTimes(1);
    expect(sendMessage).toHaveBeenCalledWith(SCROLL_TO_BLOCK_MESSAGE, { blockId: 'section-7' });
  });

  it('selects an added section under the id it was stored as', () => {
    const { editor, setSelectedBlock, loadPreview } = createEditor(
      {
        blocks: { fresh: { id: 'fresh' } },
        regions: [{ name: 'content', blocks: ['fresh'] }],
      },
      'fresh'
    );

    createBagistoPreviewRefresh(editor).refresh(createUpdates({ added: ['fresh'] }));

    loadPreview({
      blocks: { 'section-12': { id: 'section-12' } },
      regions: [{ name: 'content', blocks: ['section-12'] }],
    });

    expect(setSelectedBlock).toHaveBeenCalledWith('section-12');
  });

  it('leaves selection alone when the selected block is gone and is not the one that changed', () => {
    const { editor, setSelectedBlock, loadPreview } = createEditor(page, 'section-9');

    createBagistoPreviewRefresh(editor).refresh(createUpdates({ updated: ['section-7'] }));
    loadPreview();

    expect(setSelectedBlock).not.toHaveBeenCalled();
  });

  it('scrolls once, not on every later reload', () => {
    const { editor, sendMessage, loadPreview } = createEditor(page);

    createBagistoPreviewRefresh(editor).refresh(createUpdates({ updated: ['section-7'] }));

    loadPreview();
    loadPreview();

    expect(sendMessage).toHaveBeenCalledTimes(1);
  });

  it('reloads without scrolling when nothing names the block that changed', () => {
    const { editor, sendMessage, reload, loadPreview } = createEditor(page);

    createBagistoPreviewRefresh(editor).refresh(createUpdates({}));
    loadPreview();

    expect(reload).toHaveBeenCalledTimes(1);
    expect(sendMessage).not.toHaveBeenCalled();
  });
});
