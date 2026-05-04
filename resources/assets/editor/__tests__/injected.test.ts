import { describe, it, expect, beforeEach, vi } from 'vitest';
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
