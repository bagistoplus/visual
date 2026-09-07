import { afterEach, describe, it, expect, vi } from 'vitest';
import type { UpdatesEvent } from '@craftile/types';

const { persistUpdatesMock } = vi.hoisted(() => ({
  persistUpdatesMock: vi.fn(),
}));

vi.mock('../../../api', () => ({
  persistUpdates: persistUpdatesMock,
}));

const { removeUrlParamMock } = vi.hoisted(() => ({
  removeUrlParamMock: vi.fn(),
}));

vi.mock('../../../utils/urlState', () => ({
  removeUrlParam: removeUrlParamMock,
}));

import {
  mergeUpdates,
  hasChanges,
  determineBlocksToProcess,
  findClosestRepeated,
  computeEffects,
  extractPageDataFromHtml,
  patchResolvedBlocksFromHtml,
  collectVanishedDescendants,
  setupUpdatePersistence,
} from '../../../craftile/features/updatePersistence';
import {
  canonicalizePage,
  clearResolvedTranslationRefs,
} from '../../../utils/resolvedTranslationRefs';

afterEach(() => {
  vi.useRealTimers();
  vi.restoreAllMocks();
  persistUpdatesMock.mockReset();
  removeUrlParamMock.mockReset();
});

function createUpdatesEvent(
  changes: {
    added?: string[];
    updated?: string[];
    removed?: string[];
    moved?: Record<string, any>;
    positions?: Record<string, any>;
  },
  blocks: Record<string, any> = {},
  regions: any[] = []
): UpdatesEvent {
  return {
    changes: {
      added: changes.added || [],
      updated: changes.updated || [],
      removed: changes.removed || [],
      moved: changes.moved as any || {},
      positions: changes.positions as any || {},
    },
    blocks: blocks as any,
    regions: regions as any,
  };
}

function createControlledRequest() {
  const successCallbacks: Array<(response: string) => void> = [];
  const errorCallbacks: Array<(error: Error) => void> = [];
  const finishCallbacks: Array<() => void> = [];
  let resolveExecution: (() => void) | undefined;

  return {
    onSuccess(callback: (response: string) => void) {
      successCallbacks.push(callback);
    },
    onError(callback: (error: Error) => void) {
      errorCallbacks.push(callback);
    },
    onFinish(callback: () => void) {
      finishCallbacks.push(callback);
    },
    execute: vi.fn(
      () =>
        new Promise<void>((resolve) => {
          resolveExecution = resolve;
        })
    ),
    succeed(response: string) {
      successCallbacks.forEach((callback) => callback(response));
      finishCallbacks.forEach((callback) => callback());
      resolveExecution?.();
    },
    fail(error: Error) {
      errorCallbacks.forEach((callback) => callback(error));
      finishCallbacks.forEach((callback) => callback());
      resolveExecution?.();
    },
  };
}

function pageResponse(blocks: Record<string, any>): string {
  const renderedBlocks = Object.keys(blocks)
    .map((blockId) => `<div data-block="${blockId}"></div>`)
    .join('');

  return `
    <html>
      <head>
        <script id="page-data" type="application/json">${JSON.stringify({
          content: {
            blocks,
            regions: [{ id: 'main', name: 'Main', blocks: Object.keys(blocks) }],
          },
        })}</script>
      </head>
      <body>${renderedBlocks}</body>
    </html>
  `;
}

function createPersistenceHarness(initialBlocks: Record<string, any>) {
  let page = {
    blocks: structuredClone(initialBlocks),
    regions: [{ id: 'main', name: 'Main', blocks: Object.keys(initialBlocks) }],
  };
  let updatesHandler: ((updates: UpdatesEvent) => void) | undefined;
  const replacePageState = vi.fn((newPage) => {
    page = structuredClone(newPage);
  });
  const sendMessage = vi.fn();
  const toast = vi.fn();
  const editor = {
    engine: {
      getPage: vi.fn(() => structuredClone(page)),
      getBlockById: vi.fn((blockId: string) => structuredClone(page.blocks[blockId])),
      getBlockSchema: vi.fn(() => undefined),
      replacePageState,
      emit: vi.fn(),
      on: vi.fn(),
    },
    events: {
      on: vi.fn((event: string, handler: (updates: UpdatesEvent) => void) => {
        if (event === 'updates') {
          updatesHandler = handler;
        }
      }),
    },
    preview: {
      sendMessage,
    },
    ui: {
      toast,
    },
  } as any;

  setupUpdatePersistence(editor, { haveEdits: false } as any);

  return {
    emitUpdates(updates: UpdatesEvent) {
      updatesHandler!(updates);
    },
    replacePageState,
    sendMessage,
    toast,
  };
}

describe('updatePersistence utilities', () => {
  describe('setupUpdatePersistence', () => {
    it('discards stale same-block responses and persists only the latest merged snapshot', async () => {
      vi.useFakeTimers();

      const firstRequest = createControlledRequest();
      const secondRequest = createControlledRequest();
      persistUpdatesMock.mockReturnValueOnce(firstRequest).mockReturnValueOnce(secondRequest);

      const initialBlock = { id: 'hero', type: 'hero', properties: { text: 'Initial' }, children: [] };
      const harness = createPersistenceHarness({ hero: initialBlock });
      const firstUpdate = createUpdatesEvent(
        { updated: ['hero'] },
        { hero: { ...initialBlock, properties: { text: 'Shu' } } }
      );
      const latestUpdate = createUpdatesEvent(
        { updated: ['hero'] },
        { hero: { ...initialBlock, properties: { text: 'Shup' } } }
      );

      harness.emitUpdates(firstUpdate);
      await vi.advanceTimersByTimeAsync(300);
      expect(persistUpdatesMock).toHaveBeenCalledTimes(1);

      harness.emitUpdates(latestUpdate);
      firstRequest.succeed(pageResponse(firstUpdate.blocks));

      await vi.waitFor(() => expect(persistUpdatesMock).toHaveBeenCalledTimes(2));

      expect(harness.replacePageState).not.toHaveBeenCalled();
      expect(harness.sendMessage).not.toHaveBeenCalledWith('updates.effects', expect.anything());
      expect(persistUpdatesMock.mock.calls[1][0].changes.updated).toEqual(['hero']);
      expect(persistUpdatesMock.mock.calls[1][0].blocks.hero.properties.text).toBe('Shup');

      secondRequest.succeed(pageResponse(latestUpdate.blocks));
      await vi.waitFor(() => expect(harness.replacePageState).toHaveBeenCalledTimes(1));

      expect(harness.sendMessage).toHaveBeenCalledWith(
        'updates.effects',
        expect.objectContaining({
          changes: expect.objectContaining({ updated: ['hero'] }),
        })
      );
    });

    it('carries different-block updates into the next request when discarding a stale response', async () => {
      vi.useFakeTimers();

      const firstRequest = createControlledRequest();
      const secondRequest = createControlledRequest();
      persistUpdatesMock.mockReturnValueOnce(firstRequest).mockReturnValueOnce(secondRequest);

      const hero = { id: 'hero', type: 'hero', properties: { text: 'Initial' }, children: [] };
      const footer = { id: 'footer', type: 'footer', properties: { text: 'Initial' }, children: [] };
      const harness = createPersistenceHarness({ hero, footer });
      const heroUpdate = createUpdatesEvent(
        { updated: ['hero'] },
        { hero: { ...hero, properties: { text: 'Updated hero' } } }
      );
      const footerUpdate = createUpdatesEvent(
        { updated: ['footer'] },
        { footer: { ...footer, properties: { text: 'Updated footer' } } }
      );

      harness.emitUpdates(heroUpdate);
      await vi.advanceTimersByTimeAsync(300);
      harness.emitUpdates(footerUpdate);
      firstRequest.succeed(pageResponse(heroUpdate.blocks));

      await vi.waitFor(() => expect(persistUpdatesMock).toHaveBeenCalledTimes(2));

      const replayedUpdates = persistUpdatesMock.mock.calls[1][0];
      expect(replayedUpdates.changes.updated).toEqual(['hero', 'footer']);
      expect(Object.keys(replayedUpdates.blocks)).toEqual(['hero', 'footer']);
      expect(replayedUpdates.blocks.hero.properties.text).toBe('Updated hero');
      expect(replayedUpdates.blocks.footer.properties.text).toBe('Updated footer');
    });

    it('retains failed updates for the next persistence attempt', async () => {
      vi.useFakeTimers();
      vi.spyOn(console, 'error').mockImplementation(() => {});

      const firstRequest = createControlledRequest();
      const secondRequest = createControlledRequest();
      persistUpdatesMock.mockReturnValueOnce(firstRequest).mockReturnValueOnce(secondRequest);

      const hero = { id: 'hero', type: 'hero', properties: { text: 'Initial' }, children: [] };
      const footer = { id: 'footer', type: 'footer', properties: { text: 'Initial' }, children: [] };
      const harness = createPersistenceHarness({ hero, footer });
      const heroUpdate = createUpdatesEvent(
        { updated: ['hero'] },
        { hero: { ...hero, properties: { text: 'Updated hero' } } }
      );
      const footerUpdate = createUpdatesEvent(
        { updated: ['footer'] },
        { footer: { ...footer, properties: { text: 'Updated footer' } } }
      );

      harness.emitUpdates(heroUpdate);
      await vi.advanceTimersByTimeAsync(300);
      firstRequest.fail(new Error('Server unavailable'));
      await Promise.resolve();

      expect(harness.toast).toHaveBeenCalledWith(
        expect.objectContaining({
          type: 'error',
        })
      );

      harness.emitUpdates(footerUpdate);
      await vi.advanceTimersByTimeAsync(300);

      const retriedUpdates = persistUpdatesMock.mock.calls[1][0];
      expect(retriedUpdates.changes.updated).toEqual(['hero', 'footer']);
      expect(Object.keys(retriedUpdates.blocks)).toEqual(['hero', 'footer']);
    });
  });

  describe('mergeUpdates', () => {
    it('should merge multiple updates into one', () => {
      const updates: UpdatesEvent[] = [
        createUpdatesEvent(
          { added: ['block1'], updated: ['block2'] },
          { block1: { id: 'block1' }, block2: { id: 'block2' } }
        ),
        createUpdatesEvent(
          { added: ['block3'], updated: ['block2'] },
          { block3: { id: 'block3' }, block2: { id: 'block2', updated: true } }
        ),
      ];

      const result = mergeUpdates(updates);

      expect(result.changes.added).toEqual(['block1', 'block3']);
      expect(result.changes.updated).toEqual(['block2']);
      expect(result.blocks).toEqual({
        block1: { id: 'block1' },
        block2: { id: 'block2', updated: true },
        block3: { id: 'block3' },
      });
    });

    it('should remove duplicates from added and updated', () => {
      const updates: UpdatesEvent[] = [
        createUpdatesEvent(
          { added: ['block1', 'block2'], updated: ['block3'] },
          { block1: {}, block2: {}, block3: {} }
        ),
        createUpdatesEvent(
          { added: ['block1'], updated: ['block3', 'block4'] },
          { block1: {}, block3: {}, block4: {} }
        ),
      ];

      const result = mergeUpdates(updates);

      expect(result.changes.added).toEqual(['block1', 'block2']);
      expect(result.changes.updated).toEqual(['block3', 'block4']);
    });

    it('should remove blocks from added/updated if they are in removed', () => {
      const updates: UpdatesEvent[] = [
        createUpdatesEvent(
          { added: ['block1'], updated: ['block2'] },
          { block1: {}, block2: {} }
        ),
        createUpdatesEvent(
          { removed: ['block1', 'block2'] },
          {}
        ),
      ];

      const result = mergeUpdates(updates);

      expect(result.changes.added).toEqual([]);
      expect(result.changes.updated).toEqual([]);
      expect(result.changes.removed).toEqual(['block1', 'block2']);
    });

    it('should merge moved objects', () => {
      const updates: UpdatesEvent[] = [
        createUpdatesEvent(
          { moved: { block1: 'newParent1' } },
          {}
        ),
        createUpdatesEvent(
          { moved: { block2: 'newParent2' } },
          {}
        ),
      ];

      const result = mergeUpdates(updates);

      expect(result.changes.moved).toEqual({
        block1: 'newParent1',
        block2: 'newParent2',
      });
    });

    it('should merge positions objects', () => {
      const updates: UpdatesEvent[] = [
        createUpdatesEvent(
          { positions: { block1: { parentId: 'parent1', afterId: 'sibling1' } } },
          {}
        ),
        createUpdatesEvent(
          { positions: { block2: { regionId: 'header' } } },
          {}
        ),
      ];

      const result = mergeUpdates(updates);

      expect(result.changes.positions).toEqual({
        block1: { parentId: 'parent1', afterId: 'sibling1' },
        block2: { regionId: 'header' },
      });
    });

    it('should overwrite earlier positions for the same block with later ones', () => {
      const updates: UpdatesEvent[] = [
        createUpdatesEvent(
          { positions: { block1: { parentId: 'oldParent' } } },
          {}
        ),
        createUpdatesEvent(
          { positions: { block1: { parentId: 'newParent' } } },
          {}
        ),
      ];

      const result = mergeUpdates(updates);

      expect(result.changes.positions).toEqual({
        block1: { parentId: 'newParent' },
      });
    });

    it('should use regions from the last update that has them', () => {
      const updates: UpdatesEvent[] = [
        createUpdatesEvent({}, {}, ['region1']),
        createUpdatesEvent({}, {}, []),
        createUpdatesEvent({}, {}, ['region2', 'region3']),
      ];

      const result = mergeUpdates(updates);

      expect(result.regions).toEqual(['region2', 'region3']);
    });
  });

  describe('hasChanges', () => {
    it('should return true when there are added blocks', () => {
      const updates = createUpdatesEvent({ added: ['block1'] });
      expect(hasChanges(updates)).toBe(true);
    });

    it('should return true when there are updated blocks', () => {
      const updates = createUpdatesEvent({ updated: ['block1'] });
      expect(hasChanges(updates)).toBe(true);
    });

    it('should return true when there are removed blocks', () => {
      const updates = createUpdatesEvent({ removed: ['block1'] });
      expect(hasChanges(updates)).toBe(true);
    });

    it('should return true when there are moved blocks', () => {
      const updates = createUpdatesEvent({ moved: { block1: 'newParent' } });
      expect(hasChanges(updates)).toBe(true);
    });

    it('should return false when there are no changes', () => {
      const updates = createUpdatesEvent({});
      expect(hasChanges(updates)).toBe(false);
    });
  });

  describe('findClosestRepeated', () => {
    it('should return the first repeated ancestor', () => {
      const allBlocks = {
        block1: { id: 'block1', parentId: 'block2' },
        block2: { id: 'block2', parentId: 'block3' },
        block3: { id: 'block3', parentId: 'block4', repeated: true },
        block4: { id: 'block4', parentId: null },
      };

      const result = findClosestRepeated('block1', allBlocks);

      expect(result).toBe('block3');
    });

    it('should return the block itself when it is repeated', () => {
      const allBlocks = {
        block1: { id: 'block1', parentId: 'block2', repeated: true },
        block2: { id: 'block2', parentId: null },
      };

      const result = findClosestRepeated('block1', allBlocks);

      expect(result).toBe('block1');
    });

    it('should return null if no repeated block is found', () => {
      const allBlocks = {
        block1: { id: 'block1', parentId: 'block2' },
        block2: { id: 'block2', parentId: 'block3' },
        block3: { id: 'block3', parentId: null },
      };

      const result = findClosestRepeated('block1', allBlocks);

      expect(result).toBeNull();
    });

    it('should return null if block has no parent and is not repeated', () => {
      const allBlocks = {
        block1: { id: 'block1', parentId: null },
      };

      const result = findClosestRepeated('block1', allBlocks);

      expect(result).toBeNull();
    });

    it('should stop traversal if parent does not exist', () => {
      const allBlocks = {
        block1: { id: 'block1', parentId: 'nonexistent' },
      };

      const result = findClosestRepeated('block1', allBlocks);

      expect(result).toBeNull();
    });
  });

  describe('determineBlocksToProcess', () => {
    it('should return block IDs that are not children of other updated blocks', () => {
      const updatedBlocks = {
        block1: { id: 'block1', parentId: null },
        block2: { id: 'block2', parentId: 'block1' },
      };
      const allBlocks = {
        block1: { id: 'block1', parentId: null },
        block2: { id: 'block2', parentId: 'block1' },
      };

      const result = determineBlocksToProcess(Object.keys(updatedBlocks), allBlocks);

      expect(result).toEqual(['block1']);
    });

    it('should return parent of repeated ancestor', () => {
      const updatedBlocks = {
        block1: { id: 'block1', parentId: 'block2' },
      };
      const allBlocks = {
        block1: { id: 'block1', parentId: 'block2' },
        block2: { id: 'block2', parentId: 'block3', repeated: true },
        block3: { id: 'block3', parentId: null },
      };

      const result = determineBlocksToProcess(Object.keys(updatedBlocks), allBlocks);

      expect(result).toEqual(['block3']);
    });

    it('should return parent of ghost blocks', () => {
      const updatedBlocks = {
        block1: { id: 'block1', parentId: 'block2' },
      };
      const allBlocks = {
        block1: { id: 'block1', parentId: 'block2', ghost: true },
        block2: { id: 'block2', parentId: null },
      };

      const result = determineBlocksToProcess(Object.keys(updatedBlocks), allBlocks);

      expect(result).toEqual(['block2']);
    });

    it('should remove duplicates from result', () => {
      const updatedBlocks = {
        block1: { id: 'block1', parentId: 'block3' },
        block2: { id: 'block2', parentId: 'block3' },
      };
      const allBlocks = {
        block1: { id: 'block1', parentId: 'block3', ghost: true },
        block2: { id: 'block2', parentId: 'block3', ghost: true },
        block3: { id: 'block3', parentId: null },
      };

      const result = determineBlocksToProcess(Object.keys(updatedBlocks), allBlocks);

      expect(result).toEqual(['block3']);
    });
  });

  describe('determineBlocksToProcess with moved blocks', () => {
    it('should return parent of repeated ancestor for moved block', () => {
      const allBlocks = {
        moved: { id: 'moved', parentId: 'repeated' },
        repeated: { id: 'repeated', parentId: 'wrapper', repeated: true },
        wrapper: { id: 'wrapper', parentId: null },
      };

      const result = determineBlocksToProcess(['moved'], allBlocks);

      expect(result).toEqual(['wrapper']);
    });

    it('should return moved block itself when no repeated ancestor exists', () => {
      const allBlocks = {
        moved: { id: 'moved', parentId: 'parent' },
        parent: { id: 'parent', parentId: null },
      };

      const result = determineBlocksToProcess(['moved'], allBlocks);

      expect(result).toEqual(['moved']);
    });

    it('should skip moved block whose parent is also in the input', () => {
      const allBlocks = {
        moved: { id: 'moved', parentId: 'parent' },
        parent: { id: 'parent', parentId: null },
      };

      const result = determineBlocksToProcess(['moved', 'parent'], allBlocks);

      expect(result).toEqual(['parent']);
    });

    it('should route a repeated block in the input to its parent', () => {
      const allBlocks = {
        repeated: { id: 'repeated', parentId: 'wrapper', repeated: true },
        wrapper: { id: 'wrapper', parentId: null },
      };

      const result = determineBlocksToProcess(['repeated'], allBlocks);

      expect(result).toEqual(['wrapper']);
    });
  });

  describe('computeEffects', () => {
    it('should extract CSS from link and style tags', () => {
      const html = `
        <!DOCTYPE html>
        <html>
          <head>
            <link rel="stylesheet" href="style.css">
            <style>.foo { color: red; }</style>
          </head>
          <body></body>
        </html>
      `;

      const result = computeEffects(html, []);

      expect(result.css).toHaveLength(2);
      expect(result.css[0]).toContain('<link rel="stylesheet" href="style.css">');
      expect(result.css[1]).toContain('.foo { color: red; }');
    });

    it('should extract JavaScript from script tags', () => {
      const html = `
        <!DOCTYPE html>
        <html>
          <head></head>
          <body>
            <script>console.log('test');</script>
            <script src="app.js"></script>
          </body>
        </html>
      `;

      const result = computeEffects(html, []);

      expect(result.js).toHaveLength(2);
      expect(result.js[0]).toContain("console.log('test');");
      expect(result.js[1]).toContain('src="app.js"');
    });

    it('should extract block HTML by data-block attribute', () => {
      const html = `
        <!DOCTYPE html>
        <html>
          <body>
            <div data-block="block1">Content 1</div>
            <div data-block="block2">Content 2</div>
          </body>
        </html>
      `;

      const result = computeEffects(html, ['block1', 'block2']);

      expect(result.html['block1']).toContain('data-block="block1"');
      expect(result.html['block1']).toContain('Content 1');
      expect(result.html['block2']).toContain('data-block="block2"');
      expect(result.html['block2']).toContain('Content 2');
    });

    it('should handle missing blocks gracefully', () => {
      const html = `
        <!DOCTYPE html>
        <html>
          <body>
            <div data-block="block1">Content 1</div>
          </body>
        </html>
      `;

      const result = computeEffects(html, ['block1', 'nonexistent']);

      expect(result.html['block1']).toBeDefined();
      expect(result.html['nonexistent']).toBeUndefined();
    });

    it('should return empty arrays when no effects exist', () => {
      const html = `
        <!DOCTYPE html>
        <html>
          <head></head>
          <body></body>
        </html>
      `;

      const result = computeEffects(html, []);

      expect(result.css).toEqual([]);
      expect(result.js).toEqual([]);
      expect(result.html).toEqual({});
    });
  });

  describe('extractPageDataFromHtml', () => {
    it('parses the injected page data script', () => {
      const html = `
        <!DOCTYPE html>
        <html>
          <head>
            <script type="application/json" id="page-data">
              {"content":{"blocks":{"hero":{"id":"hero","type":"hero","properties":{"title":"Resolved title"},"children":[]}},"regions":[]}}
            </script>
          </head>
          <body></body>
        </html>
      `;

      const result = extractPageDataFromHtml(html);

      expect(result.content.blocks.hero.properties.title).toBe('Resolved title');
    });

    it('returns null when page data is not present', () => {
      const result = extractPageDataFromHtml('<html><body></body></html>');

      expect(result).toBeNull();
    });
  });

  const conditionalPage = {
    blocks: {
      hero: { id: 'hero', type: 'hero', properties: {}, children: ['title', 'subtitle'] },
      title: { id: 'title', type: 'heading', parentId: 'hero', properties: {}, children: [] },
      subtitle: {
        id: 'subtitle',
        type: 'paragraph',
        parentId: 'hero',
        properties: {},
        children: ['subtitle-icon'],
        static: true,
      },
      'subtitle-icon': { id: 'subtitle-icon', type: 'icon', parentId: 'subtitle', properties: {}, children: [] },
      footer: { id: 'footer', type: 'footer', properties: {}, children: ['signup'] },
      signup: { id: 'signup', type: 'newsletter', parentId: 'footer', properties: {}, children: [], static: true },
    },
    regions: [{ id: 'main', name: 'Main', blocks: ['hero', 'footer'] }],
  };

  describe('patchResolvedBlocksFromHtml', () => {
    it('merges resolved partial blocks into the current page without replacing regions', () => {
      const previousPage = {
        blocks: {
          hero: {
            id: 'hero',
            type: 'hero',
            properties: { title: 't:block.title', subtitle: 'Keep me' },
            children: ['button'],
          },
          button: {
            id: 'button',
            type: 'button',
            parentId: 'hero',
            properties: { label: 'Buy' },
            children: [],
          },
        },
        regions: [{ id: 'main', name: 'Main', blocks: ['hero'] }],
      };
      const replacePageState = vi.fn();
      const emit = vi.fn();
      const editor = {
        engine: {
          getPage: vi.fn(() => structuredClone(previousPage)),
          getBlockSchema: vi.fn(() => ({
            type: 'hero',
            properties: [{ id: 'title', localized: true }],
          })),
          replacePageState,
          emit,
        },
      } as any;
      const html = `
        <!DOCTYPE html>
        <html>
          <head>
            <script type="application/json" id="page-data">
              {"content":{"blocks":{"hero":{"id":"hero","type":"hero","properties":{"title":"Resolved title"},"children":["button"]}},"regions":[{"id":"partial","name":"Partial","blocks":["hero"]}]}}
            </script>
          </head>
          <body></body>
        </html>
      `;

      patchResolvedBlocksFromHtml(editor, html);

      expect(replacePageState).toHaveBeenCalledWith({
        blocks: {
          hero: {
            id: 'hero',
            type: 'hero',
            properties: { title: 'Resolved title' },
            children: ['button'],
          },
          button: previousPage.blocks.button,
        },
        regions: previousPage.regions,
      });
      expect(emit).toHaveBeenCalledWith('page:set', {
        previousPage,
        newPage: {
          blocks: {
            hero: {
              id: 'hero',
              type: 'hero',
              properties: { title: 'Resolved title' },
              children: ['button'],
            },
            button: previousPage.blocks.button,
          },
          regions: previousPage.regions,
        },
      });
      expect(canonicalizePage(replacePageState.mock.calls[0][0]).blocks.hero.properties.title).toBe('t:block.title');

      clearResolvedTranslationRefs();
    });

    it('does nothing when the response has no partial blocks', () => {
      const editor = {
        engine: {
          getPage: vi.fn(),
          replacePageState: vi.fn(),
          emit: vi.fn(),
        },
      } as any;

      patchResolvedBlocksFromHtml(editor, '<html><body></body></html>');

      expect(editor.engine.getPage).not.toHaveBeenCalled();
      expect(editor.engine.replacePageState).not.toHaveBeenCalled();
      expect(editor.engine.emit).not.toHaveBeenCalled();
    });

    function pageDataHtml(blocks: Record<string, any>): string {
      return `<html><head><script type="application/json" id="page-data">${JSON.stringify({
        content: { blocks, regions: [] },
      })}</script></head><body></body></html>`;
    }

    function createPruneEditor(previousPage: any, selectedBlockId: string | null = null) {
      return {
        engine: {
          getPage: vi.fn(() => structuredClone(previousPage)),
          getBlockSchema: vi.fn(() => undefined),
          replacePageState: vi.fn(),
          emit: vi.fn(),
        },
        ui: {
          state: { selectedBlockId },
          clearSelectedBlock: vi.fn(),
        },
      } as any;
    }


    it('prunes descendants no longer listed by a rendered parent', () => {
      const editor = createPruneEditor(conditionalPage);
      const html = pageDataHtml({
        hero: { id: 'hero', type: 'hero', properties: {}, children: ['title'] },
        title: { id: 'title', type: 'heading', parentId: 'hero', properties: {}, children: [] },
      });

      patchResolvedBlocksFromHtml(editor, html);

      const newPage = editor.engine.replacePageState.mock.calls[0][0];

      expect(Object.keys(newPage.blocks).sort()).toEqual(['footer', 'hero', 'signup', 'title']);
      expect(newPage.blocks.hero.children).toEqual(['title']);
      expect(newPage.regions).toEqual(conditionalPage.regions);
      expect(editor.ui.clearSelectedBlock).not.toHaveBeenCalled();
      expect(removeUrlParamMock).not.toHaveBeenCalled();
    });

    it('leaves blocks outside the payload untouched', () => {
      const editor = createPruneEditor(conditionalPage);
      const html = pageDataHtml({
        title: { id: 'title', type: 'heading', parentId: 'hero', properties: { text: 'Hi' }, children: [] },
      });

      patchResolvedBlocksFromHtml(editor, html);

      const newPage = editor.engine.replacePageState.mock.calls[0][0];

      expect(Object.keys(newPage.blocks).sort()).toEqual(Object.keys(conditionalPage.blocks).sort());
      expect(newPage.blocks.footer.children).toEqual(['signup']);
      expect(newPage.blocks.title.properties).toEqual({ text: 'Hi' });
    });

    it('re-adds a child that reappears in the payload', () => {
      const previousPage = {
        blocks: {
          hero: { id: 'hero', type: 'hero', properties: {}, children: ['title'] },
          title: { id: 'title', type: 'heading', parentId: 'hero', properties: {}, children: [] },
        },
        regions: [{ id: 'main', name: 'Main', blocks: ['hero'] }],
      };
      const editor = createPruneEditor(previousPage);
      const html = pageDataHtml({
        hero: { id: 'hero', type: 'hero', properties: {}, children: ['title', 'subtitle'] },
        title: { id: 'title', type: 'heading', parentId: 'hero', properties: {}, children: [] },
        subtitle: { id: 'subtitle', type: 'paragraph', parentId: 'hero', properties: {}, children: [], static: true },
      });

      patchResolvedBlocksFromHtml(editor, html);

      const newPage = editor.engine.replacePageState.mock.calls[0][0];

      expect(newPage.blocks.hero.children).toEqual(['title', 'subtitle']);
      expect(newPage.blocks.subtitle).toBeDefined();
    });

    it('clears the selection and url param when the selected block is pruned', () => {
      const editor = createPruneEditor(conditionalPage, 'subtitle-icon');
      const html = pageDataHtml({
        hero: { id: 'hero', type: 'hero', properties: {}, children: ['title'] },
        title: { id: 'title', type: 'heading', parentId: 'hero', properties: {}, children: [] },
      });

      patchResolvedBlocksFromHtml(editor, html);

      expect(editor.ui.clearSelectedBlock).toHaveBeenCalledTimes(1);
      expect(removeUrlParamMock).toHaveBeenCalledWith('block');
    });

    it('keeps the selection when an unrelated block is pruned', () => {
      const editor = createPruneEditor(conditionalPage, 'title');
      const html = pageDataHtml({
        hero: { id: 'hero', type: 'hero', properties: {}, children: ['title'] },
        title: { id: 'title', type: 'heading', parentId: 'hero', properties: {}, children: [] },
      });

      patchResolvedBlocksFromHtml(editor, html);

      expect(editor.ui.clearSelectedBlock).not.toHaveBeenCalled();
      expect(removeUrlParamMock).not.toHaveBeenCalled();
    });
  });

  describe('collectVanishedDescendants', () => {
    it('collects the whole subtree of a dropped child', () => {
      const vanished = collectVanishedDescendants(conditionalPage.blocks as any, {
        hero: { id: 'hero', type: 'hero', properties: {}, children: ['title'] },
      } as any);

      expect([...vanished].sort()).toEqual(['subtitle', 'subtitle-icon']);
    });

    it('never prunes a block that is still in the payload', () => {
      const vanished = collectVanishedDescendants(conditionalPage.blocks as any, {
        hero: { id: 'hero', type: 'hero', properties: {}, children: ['title'] },
        subtitle: { id: 'subtitle', type: 'paragraph', parentId: 'footer', properties: {}, children: ['subtitle-icon'] },
      } as any);

      expect(vanished.size).toBe(0);
    });

    it('ignores payload blocks unknown to the client', () => {
      const vanished = collectVanishedDescendants(conditionalPage.blocks as any, {
        fresh: { id: 'fresh', type: 'hero', properties: {}, children: [] },
      } as any);

      expect(vanished.size).toBe(0);
    });
  });
});
