import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import {
  PAGE_DATA_TIMEOUT,
  UNSUPPORTED_PAGE_MODAL,
  isTemplateRegistered,
  markPageLoadFailed,
  setupUnsupportedPage,
} from '../../../craftile/features/unsupportedPage';
import type { State } from '../../../state';

function makeState(overrides: Partial<State> = {}): State {
  return {
    channels: [],
    channel: 'default',
    locale: 'en',
    localeInheritance: {},
    theme: null,
    templates: [{ template: 'index', label: 'Home', icon: '', previewUrl: 'https://example.test' }],
    pageData: { url: 'https://example.test', template: 'index', sources: '' },
    images: [],
    videos: [],
    categories: new Map(),
    products: new Map(),
    cmsPages: new Map(),
    haveEdits: false,
    previewLoading: true,
    unsupportedPage: null,
    templateForm: {
      type: 'product',
      name: '',
      basedOn: '__empty__',
      error: '',
      isSubmitting: false,
    },
    ...overrides,
  };
}

function makeEditor(frame: HTMLIFrameElement | null = null) {
  const preview: any = {
    _registerFrame: vi.fn((f: HTMLIFrameElement) => {
      preview.frame = f;
    }),
    getFrame: () => preview.frame,
    frame,
  };

  return {
    preview,
    engine: { setPage: vi.fn() },
    ui: { openModal: vi.fn() },
  } as any;
}

describe('isTemplateRegistered', () => {
  it('matches only names present in the templates list', () => {
    const state = makeState();

    expect(isTemplateRegistered(state, 'index')).toBe(true);
    expect(isTemplateRegistered(state, 'shop-customer-session-index')).toBe(false);
    expect(isTemplateRegistered(state, null)).toBe(false);
  });
});

describe('markPageLoadFailed', () => {
  it('clears the page, ends loading and opens the modal', () => {
    const state = makeState();
    const editor = makeEditor();

    markPageLoadFailed(editor, state);

    expect(editor.engine.setPage).toHaveBeenCalledWith({ regions: [], blocks: {} });
    expect(state.previewLoading).toBe(false);
    expect(state.pageData).toBeNull();
    expect(state.unsupportedPage).toBe('load-failed');
    expect(editor.ui.openModal).toHaveBeenCalledWith(UNSUPPORTED_PAGE_MODAL);
  });
});

describe('setupUnsupportedPage', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('marks the page as failed when no page data arrives after the frame loads', () => {
    const state = makeState();
    const editor = makeEditor();
    const frame = document.createElement('iframe');

    setupUnsupportedPage(editor, state);
    editor.preview._registerFrame(frame);

    frame.dispatchEvent(new Event('load'));
    vi.advanceTimersByTime(PAGE_DATA_TIMEOUT - 1);

    expect(editor.ui.openModal).not.toHaveBeenCalled();

    vi.advanceTimersByTime(1);

    expect(state.unsupportedPage).toBe('load-failed');
    expect(state.previewLoading).toBe(false);
    expect(editor.ui.openModal).toHaveBeenCalledWith(UNSUPPORTED_PAGE_MODAL);
  });

  it('does nothing when page data arrives before the timeout', () => {
    const state = makeState();
    const editor = makeEditor();
    const frame = document.createElement('iframe');

    setupUnsupportedPage(editor, state);
    editor.preview._registerFrame(frame);

    frame.dispatchEvent(new Event('load'));
    state.previewLoading = false;
    vi.advanceTimersByTime(PAGE_DATA_TIMEOUT);

    expect(editor.ui.openModal).not.toHaveBeenCalled();
    expect(state.unsupportedPage).toBeNull();
  });

  it('does nothing when the frame loads while nothing is pending', () => {
    const state = makeState({ previewLoading: false });
    const editor = makeEditor();
    const frame = document.createElement('iframe');

    setupUnsupportedPage(editor, state);
    editor.preview._registerFrame(frame);

    frame.dispatchEvent(new Event('load'));
    vi.advanceTimersByTime(PAGE_DATA_TIMEOUT);

    expect(editor.ui.openModal).not.toHaveBeenCalled();
  });

  it('binds an already registered frame', () => {
    const state = makeState();
    const frame = document.createElement('iframe');
    const editor = makeEditor(frame);

    setupUnsupportedPage(editor, state);

    frame.dispatchEvent(new Event('load'));
    vi.advanceTimersByTime(PAGE_DATA_TIMEOUT);

    expect(editor.ui.openModal).toHaveBeenCalledWith(UNSUPPORTED_PAGE_MODAL);
  });

  it('restarts the timeout on a new frame load', () => {
    const state = makeState();
    const editor = makeEditor();
    const frame = document.createElement('iframe');

    setupUnsupportedPage(editor, state);
    editor.preview._registerFrame(frame);

    frame.dispatchEvent(new Event('load'));
    vi.advanceTimersByTime(PAGE_DATA_TIMEOUT - 100);
    frame.dispatchEvent(new Event('load'));
    vi.advanceTimersByTime(100);

    expect(editor.ui.openModal).not.toHaveBeenCalled();

    vi.advanceTimersByTime(PAGE_DATA_TIMEOUT - 100);

    expect(editor.ui.openModal).toHaveBeenCalledTimes(1);
  });
});
