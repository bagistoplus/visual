import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import morphdom from 'morphdom';

// Mock the Craftile modules
vi.mock('@craftile/preview-client', () => {
  const PreviewClient = vi.fn(function (this: any) {
    this.on = vi.fn();
  });
  return { PreviewClient };
});

vi.mock('@craftile/preview-client-html', () => ({
  default: {
    init: vi.fn(),
  },
}));

describe('morphdom handler', () => {
  let RawHtmlRenderer: any;
  let morphdomHandler: any;

  beforeEach(async () => {
    vi.resetModules();

    const htmlModule = await import('@craftile/preview-client-html');
    RawHtmlRenderer = htmlModule.default;

    await import('../injected');

    const initCall = RawHtmlRenderer.init.mock.calls[0];
    morphdomHandler = initCall?.[1]?.morphdom?.onBeforeElUpdated;
  });

  describe('data-morph-ignore attribute', () => {
    it('should skip morphing elements with data-morph-ignore', () => {
      const fromEl = document.createElement('div');
      fromEl.setAttribute('data-morph-ignore', '');
      fromEl.textContent = 'Original content';

      const toEl = document.createElement('div');
      toEl.textContent = 'New content';

      const result = morphdomHandler(fromEl, toEl);

      expect(result).toBe(false);
      expect(fromEl.textContent).toBe('Original content');
    });

    it('should morph elements without data-morph-ignore normally', () => {
      const fromEl = document.createElement('div');
      const toEl = document.createElement('div');

      const result = morphdomHandler(fromEl, toEl);

      expect(result).toBe(true);
    });

    it('should skip morphing even with data-morph-ignore value', () => {
      const fromEl = document.createElement('div');
      fromEl.setAttribute('data-morph-ignore', 'true');

      const toEl = document.createElement('div');

      expect(morphdomHandler(fromEl, toEl)).toBe(false);
    });

    it('should check data-morph-ignore before other conditions', () => {
      const fromEl = document.createElement('div');
      fromEl.setAttribute('data-morph-ignore', '');
      fromEl.setAttribute('wire:id', 'component-123');

      const toEl = document.createElement('div');
      toEl.setAttribute('wire:id', 'component-123');
      toEl.setAttribute('wire:effects', '{}');

      expect(morphdomHandler(fromEl, toEl)).toBe(false);
    });
  });

  describe('integration with morphdom', () => {
    it('should preserve element content when data-morph-ignore is present', () => {
      const container = document.createElement('div');
      container.innerHTML = '<div data-morph-ignore>Original</div>';

      const newContainer = document.createElement('div');
      newContainer.innerHTML = '<div>Changed</div>';

      morphdom(container, newContainer, {
        onBeforeElUpdated: morphdomHandler,
      });

      expect(container.querySelector('div')?.textContent).toBe('Original');
      expect(container.querySelector('div')?.hasAttribute('data-morph-ignore')).toBe(true);
    });

    it('should update elements without data-morph-ignore', () => {
      const container = document.createElement('div');
      container.innerHTML = '<div>Original</div>';

      const newContainer = document.createElement('div');
      newContainer.innerHTML = '<div>Changed</div>';

      morphdom(container, newContainer, {
        onBeforeElUpdated: morphdomHandler,
      });

      expect(container.querySelector('div')?.textContent).toBe('Changed');
    });

    it('should handle mixed scenarios', () => {
      const container = document.createElement('div');
      container.innerHTML = `
        <div id="ignored" data-morph-ignore>Ignored content</div>
        <div id="updated">Original content</div>
      `;

      const newContainer = document.createElement('div');
      newContainer.innerHTML = `
        <div id="ignored">This should not appear</div>
        <div id="updated">Updated content</div>
      `;

      morphdom(container, newContainer, {
        onBeforeElUpdated: morphdomHandler,
      });

      expect(container.querySelector('#ignored')?.textContent).toBe('Ignored content');
      expect(container.querySelector('#updated')?.textContent).toBe('Updated content');
    });
  });
});

describe('Visual utilities', () => {
  let Visual: any;

  beforeEach(async () => {
    vi.resetModules();
    await import('../injected');
    Visual = (window as any).Visual;
  });

  describe('isResponsiveValue', () => {
    it('should return true for responsive objects with _default', () => {
      expect(Visual.isResponsiveValue({ _default: 1 })).toBe(true);
    });

    it('should return true for responsive objects with breakpoint overrides', () => {
      expect(Visual.isResponsiveValue({ _default: 1, mobile: 2, tablet: 3 })).toBe(true);
    });

    it('should return true for responsive objects with only _default', () => {
      expect(Visual.isResponsiveValue({ _default: 'base' })).toBe(true);
    });

    it('should return false for simple values', () => {
      expect(Visual.isResponsiveValue(1)).toBe(false);
      expect(Visual.isResponsiveValue('text')).toBe(false);
      expect(Visual.isResponsiveValue(true)).toBe(false);
    });

    it('should return false for null and undefined', () => {
      expect(Visual.isResponsiveValue(null)).toBe(false);
      expect(Visual.isResponsiveValue(undefined)).toBe(false);
    });

    it('should return false for arrays', () => {
      expect(Visual.isResponsiveValue([1, 2, 3])).toBe(false);
    });

    it('should return false for objects without _default', () => {
      expect(Visual.isResponsiveValue({ mobile: 1, tablet: 2 })).toBe(false);
      expect(Visual.isResponsiveValue({})).toBe(false);
    });
  });

  describe('getResponsiveValue', () => {
    it('should return device-specific value when available', () => {
      const value = { _default: 1, mobile: 2, tablet: 3 };
      expect(Visual.getResponsiveValue(value, 'mobile')).toBe(2);
      expect(Visual.getResponsiveValue(value, 'tablet')).toBe(3);
    });

    it('should fall back to _default when device is not set', () => {
      const value = { _default: 1, mobile: 2 };
      expect(Visual.getResponsiveValue(value, 'tablet')).toBe(1);
      expect(Visual.getResponsiveValue(value, 'desktop')).toBe(1);
    });

    it('should fall back to custom fallback when neither device nor _default exists', () => {
      const value = { _default: undefined } as any;
      expect(Visual.getResponsiveValue(value, 'mobile', 42)).toBe(42);
    });

    it('should return simple value directly', () => {
      expect(Visual.getResponsiveValue(5, 'mobile')).toBe(5);
      expect(Visual.getResponsiveValue('text', 'tablet')).toBe('text');
    });

    it('should return fallback for null/undefined simple values', () => {
      expect(Visual.getResponsiveValue(null, 'mobile', 10)).toBe(10);
      expect(Visual.getResponsiveValue(undefined, 'mobile', 10)).toBe(10);
    });

    it('should return simple value even when fallback is provided', () => {
      expect(Visual.getResponsiveValue(5, 'mobile', 10)).toBe(5);
    });

    it('should handle responsive value with only _default', () => {
      const value = { _default: 'base' };
      expect(Visual.getResponsiveValue(value, 'mobile')).toBe('base');
      expect(Visual.getResponsiveValue(value, 'tablet')).toBe('base');
    });
  });
});

describe('Visual editor event forwarding', () => {
  let previewClient: any;
  let emittedEvents: { type: string; detail: any }[];
  let dispatchEventSpy: ReturnType<typeof vi.spyOn>;

  beforeEach(async () => {
    vi.resetModules();
    vi.clearAllMocks();
    vi.stubGlobal('CSS', {
      escape: (value: string) => value.replace(/[^a-zA-Z0-9_-]/g, '\\$&'),
    });

    emittedEvents = [];
    dispatchEventSpy = vi.spyOn(document, 'dispatchEvent').mockImplementation((event: Event) => {
      emittedEvents.push({
        type: event.type,
        detail: (event as CustomEvent).detail,
      });

      return true;
    });

    const previewModule = await import('@craftile/preview-client');
    await import('../injected');

    previewClient = (previewModule.PreviewClient as any).mock.instances[0];
  });

  afterEach(() => {
    dispatchEventSpy.mockRestore();
    vi.unstubAllGlobals();
  });

  function triggerPreviewEvent(eventName: string, payload: any): void {
    const handlers = previewClient.on.mock.calls
      .filter(([registeredEvent]: [string]) => registeredEvent === eventName)
      .map(([, handler]: [string, (data: any) => void]) => handler);

    for (const handler of handlers) {
      handler(payload);
    }
  }

  function expectEvent(type: string, detail: any): void {
    expect(emittedEvents).toContainEqual({ type, detail });
  }

  function expectNoEvent(type: string): void {
    expect(emittedEvents.some((event) => event.type === type)).toBe(false);
  }

  it.each([
    ['block.insert.before', ['adding']],
    ['block.insert.after', ['added', 'load']],
    ['block.remove.before', ['removing']],
    ['block.remove.after', ['removed', 'unload']],
    ['block.move.before', ['moving']],
    ['block.move.after', ['moved']],
    ['block.update.before', ['updating']],
    ['block.update.after', ['updated', 'load']],
    ['block.select', ['selected']],
    ['block.deselect', ['deselected']],
  ])('emits block-scoped variants for %s', (previewEvent, visualEvents) => {
    const payload = {
      blockId: 'block-123',
      block: { id: 'block-123', parentId: 'section-123' },
    };

    triggerPreviewEvent(previewEvent, payload);

    for (const visualEvent of visualEvents) {
      expectEvent(`visual:block:${visualEvent}`, payload);
      expectEvent(`visual:block:${visualEvent}:block-123`, payload);
    }
  });

  it.each([
    ['block.insert.before', ['adding']],
    ['block.insert.after', ['added', 'load']],
    ['block.remove.before', ['removing']],
    ['block.remove.after', ['removed', 'unload']],
    ['block.move.before', ['moving']],
    ['block.move.after', ['moved']],
    ['block.update.before', ['updating']],
    ['block.update.after', ['updated', 'load']],
  ])('emits section-scoped variants for top-level blocks on %s', (previewEvent, visualEvents) => {
    const payload = {
      blockId: 'section-123',
      block: { id: 'section-123' },
    };
    const sectionPayload = {
      ...payload,
      sectionId: 'section-123',
      section: payload.block,
    };

    triggerPreviewEvent(previewEvent, payload);

    for (const visualEvent of visualEvents) {
      expectEvent(`visual:section:${visualEvent}`, sectionPayload);
      expectEvent(`visual:section:${visualEvent}:section-123`, sectionPayload);
    }
  });

  it('does not emit section events for nested blocks', () => {
    const payload = {
      blockId: 'block-123',
      block: { id: 'block-123', parentId: 'section-123' },
    };

    triggerPreviewEvent('block.insert.after', payload);

    expectNoEvent('visual:section:added');
    expectNoEvent('visual:section:added:block-123');
    expectNoEvent('visual:section:load');
    expectNoEvent('visual:section:load:block-123');
  });

  it('uses block.id as the scoped block id when blockId is missing', () => {
    const payload = {
      block: { id: 'block-from-object', parentId: 'section-123' },
    };

    triggerPreviewEvent('block.select', payload);

    expectEvent('visual:block:selected', payload);
    expectEvent('visual:block:selected:block-from-object', payload);
  });

  it.each([
    ['block.select', 'selected'],
    ['block.deselect', 'deselected'],
  ])('does not emit section events for top-level block %s payloads', (previewEvent, visualEvent) => {
    const payload = {
      blockId: 'section-123',
      block: { id: 'section-123' },
    };

    triggerPreviewEvent(previewEvent, payload);

    expectEvent(`visual:block:${visualEvent}`, payload);
    expectEvent(`visual:block:${visualEvent}:section-123`, payload);
    expectNoEvent(`visual:section:${visualEvent}`);
    expectNoEvent(`visual:section:${visualEvent}:section-123`);
  });

  it('does not emit block-scoped lifecycle variants when no block id is available', () => {
    const payload = {
      block: { parentId: 'section-123' },
    };

    triggerPreviewEvent('block.select', payload);

    expectEvent('visual:block:selected', payload);
    expectNoEvent('visual:block:selected:undefined');
  });

  it('emits setting-scoped block and section events for top-level block setting updates', () => {
    const payload = {
      blockId: 'section-123',
      block: { id: 'section-123' },
      key: 'heading',
      value: 'New heading',
      oldValue: 'Old heading',
    };

    triggerPreviewEvent('block.property.updated', payload);

    expectEvent('visual:block:setting:updated', payload);
    expectEvent('visual:block:setting:updated:heading', payload);
    expectEvent('visual:section:setting:updated', payload);
    expectEvent('visual:section:setting:updated:heading', payload);
  });

  it('does not emit section setting events for nested block setting updates', () => {
    const payload = {
      blockId: 'block-123',
      block: { id: 'block-123', parentId: 'section-123' },
      key: 'heading',
      value: 'New heading',
      oldValue: 'Old heading',
    };

    triggerPreviewEvent('block.property.updated', payload);

    expectEvent('visual:block:setting:updated', payload);
    expectEvent('visual:block:setting:updated:heading', payload);
    expectNoEvent('visual:section:setting:updated');
    expectNoEvent('visual:section:setting:updated:heading');
  });

  it('does not emit setting-scoped variants when the setting key is missing', () => {
    const payload = {
      blockId: 'block-123',
      block: { id: 'block-123', parentId: 'section-123' },
      value: 'New heading',
      oldValue: 'Old heading',
    };

    triggerPreviewEvent('block.property.updated', payload);

    expectEvent('visual:block:setting:updated', payload);
    expectNoEvent('visual:block:setting:updated:undefined');
  });
});
