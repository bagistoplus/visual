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
  let morphdomOptions: any;
  let morphdomHandler: any;
  let previewClient: any;

  beforeEach(async () => {
    vi.resetModules();
    delete (window as any).Alpine;

    const htmlModule = await import('@craftile/preview-client-html');
    RawHtmlRenderer = htmlModule.default;

    await import('../injected');

    const initCall = RawHtmlRenderer.init.mock.calls[0];
    morphdomOptions = initCall?.[1]?.morphdom;
    morphdomHandler = morphdomOptions?.onBeforeElUpdated;

    const previewModule = await import('@craftile/preview-client');
    previewClient = (previewModule.PreviewClient as any).mock.instances[0];
  });

  afterEach(() => {
    delete (window as any).Alpine;
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

  describe('node keying', () => {
    it('gives morphdom a getNodeKey resolver', () => {
      expect(typeof morphdomOptions?.getNodeKey).toBe('function');
    });

    it('prefers data-block, then wire:key, then id', () => {
      const el = document.createElement('div');
      el.setAttribute('data-block', 'block-key');
      el.setAttribute('wire:key', 'wire-key');
      el.id = 'element-key';

      expect(morphdomOptions.getNodeKey(el)).toBe('block-key');

      el.removeAttribute('data-block');
      expect(morphdomOptions.getNodeKey(el)).toBe('wire-key');

      el.removeAttribute('wire:key');
      expect(morphdomOptions.getNodeKey(el)).toBe('element-key');
    });

    it('returns undefined for unkeyed elements and non-element nodes', () => {
      expect(morphdomOptions.getNodeKey(document.createElement('div'))).toBeUndefined();
      expect(morphdomOptions.getNodeKey(document.createTextNode('text'))).toBeUndefined();
      expect(morphdomOptions.getNodeKey(document.createComment('comment'))).toBeUndefined();
    });

    it('moves keyed blocks instead of rewriting them when siblings are reordered', () => {
      const container = document.createElement('div');
      container.innerHTML = '<div data-block="first">First</div><div data-block="second">Second</div>';
      const [first, second] = Array.from(container.children);

      const newContainer = document.createElement('div');
      newContainer.innerHTML =
        '<div data-block="second">Second</div><div data-block="first">First</div>';

      morphdom(container, newContainer, morphdomOptions);

      expect(Array.from(container.children)).toEqual([second, first]);
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

  describe('Livewire and Alpine morphing', () => {
    it('morphs a Livewire root with Alpine while preserving its Livewire envelope', () => {
      const mergeNewSnapshot = vi.fn();
      const processEffects = vi.fn();
      const alpineState = { open: true };
      const alpineMorph = vi.fn((fromEl: HTMLElement, toEl: HTMLElement) => {
        for (const attribute of Array.from(fromEl.attributes)) {
          if (!toEl.hasAttribute(attribute.name)) {
            fromEl.removeAttribute(attribute.name);
          }
        }

        for (const attribute of Array.from(toEl.attributes)) {
          fromEl.setAttribute(attribute.name, attribute.value);
        }

        fromEl.innerHTML = toEl.innerHTML;
      });

      (window as any).Alpine = { morph: alpineMorph };

      const container = document.createElement('div');
      const fromEl = document.createElement('section');
      fromEl.setAttribute('data-block', 'hero');
      fromEl.setAttribute('wire:id', 'existing-component');
      fromEl.setAttribute('wire:snapshot', 'existing-snapshot');
      fromEl.setAttribute('wire:effects', '{"listeners":["existing"]}');
      fromEl.setAttribute('wire:key', 'existing-key');
      fromEl.setAttribute('class', 'old-class');
      (fromEl as any)._x_dataStack = [alpineState];
      (fromEl as any).__livewire = { mergeNewSnapshot, processEffects };
      fromEl.textContent = 'Old heading';
      container.appendChild(fromEl);

      const toEl = document.createElement('section');
      toEl.setAttribute('data-block', 'hero');
      toEl.setAttribute('wire:id', 'fresh-component');
      toEl.setAttribute('wire:snapshot', 'fresh-snapshot');
      toEl.setAttribute('wire:effects', '{"listeners":["fresh"]}');
      toEl.setAttribute('wire:key', 'fresh-key');
      toEl.setAttribute('class', 'new-class');
      toEl.textContent = 'New heading';

      expect(morphdomHandler(fromEl, toEl)).toBe(false);
      expect(alpineMorph).toHaveBeenCalledOnce();
      expect(container.firstElementChild).toBe(fromEl);
      expect(fromEl.className).toBe('new-class');
      expect(fromEl.textContent).toBe('New heading');
      expect((fromEl as any)._x_dataStack[0]).toBe(alpineState);
      expect(fromEl.getAttribute('wire:id')).toBe('existing-component');
      expect(fromEl.getAttribute('wire:snapshot')).toBe('existing-snapshot');
      expect(fromEl.getAttribute('wire:effects')).toBe('{"listeners":["existing"]}');
      expect(fromEl.getAttribute('wire:key')).toBe('existing-key');
      expect(mergeNewSnapshot).not.toHaveBeenCalled();
      expect(processEffects).not.toHaveBeenCalled();
    });

    it('updates the active Livewire root and morphs nested Livewire children only', () => {
      const alpineMorph = vi.fn();
      (window as any).Alpine = { morph: alpineMorph };

      const fromEl = document.createElement('section');
      fromEl.setAttribute('wire:id', 'existing-component');
      fromEl.setAttribute('wire:snapshot', 'existing-snapshot');

      const toEl = document.createElement('section');
      toEl.setAttribute('wire:id', 'fresh-component');
      toEl.setAttribute('wire:snapshot', 'fresh-snapshot');

      morphdomHandler(fromEl, toEl);

      const options = alpineMorph.mock.calls[0][2];
      const rootChildrenOnly = vi.fn();
      const rootSkip = vi.fn();

      options.updating(fromEl, toEl, rootChildrenOnly, rootSkip);

      expect(rootChildrenOnly).not.toHaveBeenCalled();
      expect(rootSkip).not.toHaveBeenCalled();

      const nestedFrom = document.createElement('div');
      nestedFrom.setAttribute('wire:id', 'nested-existing');
      const nestedTo = document.createElement('div');
      nestedTo.setAttribute('wire:id', 'nested-fresh');
      const nestedChildrenOnly = vi.fn();

      options.updating(nestedFrom, nestedTo, nestedChildrenOnly, vi.fn());

      expect(nestedChildrenOnly).toHaveBeenCalledOnce();
    });

    it('skips ignored descendants inside the Alpine morph', () => {
      const alpineMorph = vi.fn();
      (window as any).Alpine = { morph: alpineMorph };

      const fromEl = document.createElement('section');
      fromEl.setAttribute('wire:id', 'existing-component');
      const toEl = document.createElement('section');
      toEl.setAttribute('wire:id', 'fresh-component');

      morphdomHandler(fromEl, toEl);

      const ignoredFrom = document.createElement('div');
      ignoredFrom.setAttribute('data-morph-ignore', '');
      const ignoredTo = document.createElement('div');
      const skip = vi.fn();

      alpineMorph.mock.calls[0][2].updating(ignoredFrom, ignoredTo, vi.fn(), skip);

      expect(skip).toHaveBeenCalledOnce();
    });

    it('skips recently live-updated descendants inside the Alpine morph', () => {
      vi.stubGlobal('CSS', {
        escape: (value: string) => value.replace(/[^a-zA-Z0-9_-]/g, '\\$&'),
      });

      const alpineMorph = vi.fn();
      (window as any).Alpine = { morph: alpineMorph };

      const fromEl = document.createElement('section');
      fromEl.setAttribute('wire:id', 'existing-component');
      const toEl = document.createElement('section');
      toEl.setAttribute('wire:id', 'fresh-component');
      morphdomHandler(fromEl, toEl);

      const liveUpdatedFrom = document.createElement('div');
      liveUpdatedFrom.setAttribute('data-live-update-block-123.heading', 'text');
      document.body.appendChild(liveUpdatedFrom);

      const propertyUpdateHandler = previewClient.on.mock.calls.find(
        ([eventName]: [string]) => eventName === 'block.property.updated'
      )[1];

      propertyUpdateHandler({
        block: { id: 'block-123' },
        key: 'heading',
        value: 'Fresh heading',
        oldValue: 'Old heading',
      });

      const skip = vi.fn();
      alpineMorph.mock.calls[0][2].updating(
        liveUpdatedFrom,
        document.createElement('div'),
        vi.fn(),
        skip
      );

      expect(skip).toHaveBeenCalledOnce();

      liveUpdatedFrom.remove();
      vi.unstubAllGlobals();
    });

    it('uses stable Visual and DOM keys for Alpine morphing', () => {
      const alpineMorph = vi.fn();
      (window as any).Alpine = { morph: alpineMorph };

      const fromEl = document.createElement('section');
      fromEl.setAttribute('wire:id', 'existing-component');
      const toEl = document.createElement('section');
      toEl.setAttribute('wire:id', 'fresh-component');

      morphdomHandler(fromEl, toEl);

      const key = alpineMorph.mock.calls[0][2].key;
      const block = document.createElement('div');
      block.setAttribute('data-block', 'block-key');
      block.setAttribute('wire:key', 'wire-key');
      block.id = 'element-key';

      expect(key(block)).toBe('block-key');

      block.removeAttribute('data-block');
      expect(key(block)).toBe('wire-key');

      block.removeAttribute('wire:key');
      expect(key(block)).toBe('element-key');
    });

    it('continues to morph simple Alpine components', () => {
      const alpineMorph = vi.fn();
      (window as any).Alpine = { morph: alpineMorph };

      const fromEl = document.createElement('div');
      const toEl = document.createElement('div');
      (fromEl as any)._x_dataStack = [{ open: true }];

      expect(morphdomHandler(fromEl, toEl)).toBe(false);
      expect(alpineMorph).toHaveBeenCalledWith(fromEl, toEl, expect.any(Object));
    });

    it.each([
      ['Alpine Morph is unavailable', 'section', 'section', undefined],
      ['the root tag changes', 'section', 'div', { morph: vi.fn() }],
    ])('replaces the Livewire root when %s', (_, fromTag, toTag, Alpine) => {
      (window as any).Alpine = Alpine;

      const container = document.createElement('div');
      const fromEl = document.createElement(fromTag);
      fromEl.setAttribute('wire:id', 'existing-component');
      container.appendChild(fromEl);

      const toEl = document.createElement(toTag);
      toEl.setAttribute('wire:id', 'fresh-component');

      expect(morphdomHandler(fromEl, toEl)).toBe(false);
      expect(container.firstElementChild).toBe(toEl);
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

describe('external link interception', () => {
  let confirmSpy: any;
  let openSpy: any;

  beforeEach(async () => {
    vi.resetModules();
    confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true);
    openSpy = vi.spyOn(window, 'open').mockImplementation(() => null);

    await import('../injected');
  });

  afterEach(() => {
    confirmSpy.mockRestore();
    openSpy.mockRestore();
    document.body.innerHTML = '';
  });

  function clickLink(attributes: Record<string, string>, init: MouseEventInit = {}) {
    const anchor = document.createElement('a');

    Object.entries(attributes).forEach(([name, value]) => anchor.setAttribute(name, value));
    document.body.appendChild(anchor);

    const event = new MouseEvent('click', { bubbles: true, cancelable: true, button: 0, ...init });
    anchor.dispatchEvent(event);

    return event;
  }

  it('asks for confirmation and opens external links in a new window', () => {
    const event = clickLink({ href: 'https://themefullstack.com/' });

    expect(event.defaultPrevented).toBe(true);
    expect(confirmSpy).toHaveBeenCalledWith(expect.stringContaining('https://themefullstack.com/'));
    expect(openSpy).toHaveBeenCalledWith('https://themefullstack.com/', '_blank');
  });

  it('does not open the link when the confirmation is cancelled', () => {
    confirmSpy.mockReturnValue(false);

    const event = clickLink({ href: 'https://themefullstack.com/' });

    expect(event.defaultPrevented).toBe(true);
    expect(openSpy).not.toHaveBeenCalled();
  });

  it('ignores same origin links', () => {
    const event = clickLink({ href: '/products/shoes' });

    expect(event.defaultPrevented).toBe(false);
    expect(confirmSpy).not.toHaveBeenCalled();
  });

  it('ignores links that already open in a new tab, downloads and non http schemes', () => {
    clickLink({ href: 'https://themefullstack.com/', target: '_blank' });
    clickLink({ href: 'https://themefullstack.com/file.zip', download: '' });
    clickLink({ href: 'mailto:hello@example.com' });
    clickLink({ href: 'tel:+123456' });

    expect(confirmSpy).not.toHaveBeenCalled();
  });

  it('ignores modifier and middle button clicks', () => {
    clickLink({ href: 'https://themefullstack.com/' }, { metaKey: true });
    clickLink({ href: 'https://themefullstack.com/' }, { ctrlKey: true });
    clickLink({ href: 'https://themefullstack.com/' }, { button: 1 });

    expect(confirmSpy).not.toHaveBeenCalled();
  });
});
