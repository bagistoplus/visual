import { describe, expect, it, vi } from 'vitest';
import { setupPreviewLoading } from '../../../craftile/features/previewLoading';
import type { State } from '../../../state';

vi.mock('vue', async (importOriginal) => {
  const actual = await importOriginal<typeof import('vue')>();

  return {
    ...actual,
    createApp: vi.fn(() => ({
      mount: vi.fn(),
    })),
  };
});

function makeState(overrides: Partial<State> = {}): State {
  return {
    channels: [],
    channel: 'default',
    locale: 'en',
    localeInheritance: {},
    theme: null,
    templates: [],
    pageData: null,
    images: [],
    videos: [],
    categories: new Map(),
    products: new Map(),
    cmsPages: new Map(),
    haveEdits: false,
    previewLoading: false,
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

describe('preview loading state', () => {
  it('sets preview loading and clears locale inheritance before loading a new url', () => {
    const state = makeState({
      localeInheritance: {
        ar: { parentChannel: 'default', parentLocale: 'en' },
      },
    });
    const loadUrl = vi.fn();
    const editor = {
      preview: {
        loadUrl,
        reload: vi.fn(),
      },
    } as any;

    setupPreviewLoading(editor, state);
    editor.preview.loadUrl('https://example.test');

    expect(state.previewLoading).toBe(true);
    expect(state.localeInheritance).toEqual({});
    expect(loadUrl).toHaveBeenCalledWith('https://example.test');
  });

  it('sets preview loading and clears locale inheritance before reloading the preview', () => {
    const state = makeState({
      localeInheritance: {
        ar: { parentChannel: 'default', parentLocale: 'en' },
      },
    });
    const reload = vi.fn();
    const editor = {
      preview: {
        loadUrl: vi.fn(),
        reload,
      },
    } as any;

    setupPreviewLoading(editor, state);
    editor.preview.reload();

    expect(state.previewLoading).toBe(true);
    expect(state.localeInheritance).toEqual({});
    expect(reload).toHaveBeenCalled();
  });
});
