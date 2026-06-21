import { describe, expect, it, vi } from 'vitest';
import { setupPageDataHandler, syncEditorContextFromPageData } from '../../../craftile/handlers/pageData';
import type { State } from '../../../state';

vi.mock('nprogress', () => ({
  default: {
    done: vi.fn(),
  },
}));

function makeState(overrides: Partial<State> = {}): State {
  return {
    channels: [
      {
        code: 'default',
        name: 'Default',
        default_locale: 'en',
        locales: [
          { code: 'en', name: 'English', logo_url: '' },
          { code: 'fr', name: 'French', logo_url: '' },
        ],
      },
      {
        code: 'mobile',
        name: 'Mobile',
        default_locale: 'fr',
        locales: [{ code: 'fr', name: 'French', logo_url: '' }],
      },
    ],
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

describe('preview page data context sync', () => {
  it('syncs locale from preview page data', () => {
    const state = makeState();

    syncEditorContextFromPageData(state, { locale: 'fr' });

    expect(state.locale).toBe('fr');
  });

  it('syncs channel and locale from preview page data', () => {
    const state = makeState();

    syncEditorContextFromPageData(state, { channel: 'mobile', locale: 'fr' });

    expect(state.channel).toBe('mobile');
    expect(state.locale).toBe('fr');
  });

  it('replaces locale inheritance from preview page data', () => {
    const state = makeState({
      localeInheritance: {
        fr: { parentChannel: 'default', parentLocale: 'en' },
      },
    });

    syncEditorContextFromPageData(state, {
      localeInheritance: {
        en: { parentChannel: 'mobile', parentLocale: 'fr' },
      },
    });

    expect(state.localeInheritance).toEqual({
      en: { parentChannel: 'mobile', parentLocale: 'fr' },
    });
  });

  it('clears locale inheritance when preview page data omits it', () => {
    const state = makeState({
      localeInheritance: {
        fr: { parentChannel: 'default', parentLocale: 'en' },
      },
    });

    syncEditorContextFromPageData(state, {});

    expect(state.localeInheritance).toEqual({});
  });

  it('trusts preview locale even when it is not listed for the current channel', () => {
    const state = makeState({ locale: 'en' });

    syncEditorContextFromPageData(state, { channel: 'mobile', locale: 'en' });

    expect(state.channel).toBe('mobile');
    expect(state.locale).toBe('en');
  });

  it('trusts preview channel even when it is not in editor config', () => {
    const state = makeState();

    syncEditorContextFromPageData(state, { channel: 'unknown', locale: 'fr' });

    expect(state.channel).toBe('unknown');
    expect(state.locale).toBe('fr');
  });

  it('applies preview block schemas before page content', () => {
    const state = makeState({ previewLoading: true });
    const calls: string[] = [];
    let handler: any;
    const blocksManager = {
      has: vi.fn(() => true),
      unregister: vi.fn(() => calls.push('unregister')),
      register: vi.fn(() => calls.push('register')),
    };
    const editor = {
      preview: {
        onReady: (callback: Function) => callback(),
        onMessage: (_event: string, callback: Function) => {
          handler = callback;
        },
      },
      engine: {
        getBlocksManager: () => blocksManager,
        setPage: vi.fn(() => calls.push('setPage')),
      },
    } as any;

    setupPageDataHandler(editor, state);

    handler({
      pageData: {
        content: { blocks: {}, regions: [] },
        blockSchemas: [{ type: 'hero' }],
        template: {
          url: 'https://example.test',
          name: 'index',
          sources: 'encrypted',
        },
      },
    });

    expect(blocksManager.unregister).toHaveBeenCalledWith('hero');
    expect(blocksManager.register).toHaveBeenCalledWith('hero', { type: 'hero' });
    expect(editor.engine.setPage).toHaveBeenCalledWith({ blocks: {}, regions: [] });
    expect(state.previewLoading).toBe(false);
    expect(calls).toEqual(['unregister', 'register', 'setPage']);
  });
});
