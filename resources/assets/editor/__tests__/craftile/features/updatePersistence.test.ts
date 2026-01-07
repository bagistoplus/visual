import { describe, it, expect } from 'vitest';
import type { UpdatesEvent } from '@craftile/types';
import {
  mergeUpdates,
  hasChanges,
  determineBlocksToProcess,
  findRepeatedAncestor,
  computeEffects,
} from '../../../craftile/features/updatePersistence';

function createUpdatesEvent(
  changes: { added?: string[]; updated?: string[]; removed?: string[]; moved?: Record<string, any> },
  blocks: Record<string, any> = {},
  regions: any[] = []
): UpdatesEvent {
  return {
    changes: {
      added: changes.added || [],
      updated: changes.updated || [],
      removed: changes.removed || [],
      moved: changes.moved as any || {},
    },
    blocks: blocks as any,
    regions: regions as any,
  };
}

describe('updatePersistence utilities', () => {
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

  describe('findRepeatedAncestor', () => {
    it('should return the first repeated ancestor', () => {
      const allBlocks = {
        block1: { id: 'block1', parentId: 'block2' },
        block2: { id: 'block2', parentId: 'block3' },
        block3: { id: 'block3', parentId: 'block4', repeated: true },
        block4: { id: 'block4', parentId: null },
      };

      const result = findRepeatedAncestor('block1', allBlocks);

      expect(result).toBe('block3');
    });

    it('should return null if no repeated ancestor exists', () => {
      const allBlocks = {
        block1: { id: 'block1', parentId: 'block2' },
        block2: { id: 'block2', parentId: 'block3' },
        block3: { id: 'block3', parentId: null },
      };

      const result = findRepeatedAncestor('block1', allBlocks);

      expect(result).toBeNull();
    });

    it('should return null if block has no parent', () => {
      const allBlocks = {
        block1: { id: 'block1', parentId: null },
      };

      const result = findRepeatedAncestor('block1', allBlocks);

      expect(result).toBeNull();
    });

    it('should stop traversal if parent does not exist', () => {
      const allBlocks = {
        block1: { id: 'block1', parentId: 'nonexistent' },
      };

      const result = findRepeatedAncestor('block1', allBlocks);

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

      const result = determineBlocksToProcess(updatedBlocks, allBlocks);

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

      const result = determineBlocksToProcess(updatedBlocks, allBlocks);

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

      const result = determineBlocksToProcess(updatedBlocks, allBlocks);

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

      const result = determineBlocksToProcess(updatedBlocks, allBlocks);

      expect(result).toEqual(['block3']);
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
});
