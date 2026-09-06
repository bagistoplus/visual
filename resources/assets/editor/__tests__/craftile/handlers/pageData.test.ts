import { describe, expect, it, vi } from 'vitest';
import { setupPageDataHandler, syncEditorContextFromPageData } from '../../../craftile/handlers/pageData';
import type { State } from '../../../state';
import { canonicalizePage, recordResolvedTranslationRefs } from '../../../utils/resolvedTranslationRefs';

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
    templates: [{ template: 'index', label: 'Home', icon: '', previewUrl: 'https://example.test' }],
    pageData: null,
    images: [],
    videos: [],
    categories: new Map(),
    products: new Map(),
    cmsPages: new Map(),
    haveEdits: false,
    previewLoading: false,
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
        getBlockSchema: vi.fn(() => undefined),
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

  it('clears resolved translation refs when full page data is handled', () => {
    recordResolvedTranslationRefs(
      {
        hero: {
          id: 'hero',
          type: 'hero',
          properties: { title: 't:block.title' },
          children: [],
        },
      },
      {
        hero: {
          id: 'hero',
          type: 'hero',
          properties: { title: 'Resolved title' },
          children: [],
        },
      },
      () => ({
        type: 'hero',
        properties: [{ id: 'title', localized: true }],
      } as any)
    );

    const state = makeState();
    let handler: any;
    const editor = {
      preview: {
        onReady: (callback: Function) => callback(),
        onMessage: (_event: string, callback: Function) => {
          handler = callback;
        },
      },
      engine: {
        getBlocksManager: () => ({
          has: vi.fn(() => false),
          register: vi.fn(),
        }),
        getBlockSchema: vi.fn(() => undefined),
        setPage: vi.fn(),
      },
    } as any;

    setupPageDataHandler(editor, state);

    handler({
      pageData: {
        content: { blocks: {}, regions: [] },
        template: {
          url: 'https://example.test',
          name: 'index',
          sources: 'encrypted',
        },
      },
    });

    const canonical = canonicalizePage({
      blocks: {
        hero: {
          id: 'hero',
          type: 'hero',
          properties: { title: 'Resolved title' },
          children: [],
        },
      },
      regions: [{ name: 'main', blocks: ['hero'] }],
    });

    expect(canonical.blocks.hero.properties.title).toBe('Resolved title');
  });

  it('seeds resolved translation refs from full page data translation references', () => {
    const state = makeState();
    let handler: any;
    const editor = {
      preview: {
        onReady: (callback: Function) => callback(),
        onMessage: (_event: string, callback: Function) => {
          handler = callback;
        },
      },
      engine: {
        getBlocksManager: () => ({
          has: vi.fn(() => false),
          register: vi.fn(),
        }),
        getBlockSchema: vi.fn(() => ({
          type: 'hero',
          properties: [
            { id: 'title', localized: true },
            { id: 'color', localized: false },
          ],
        })),
        setPage: vi.fn(),
      },
    } as any;

    setupPageDataHandler(editor, state);

    handler({
      pageData: {
        content: {
          blocks: {
            hero: {
              id: 'hero',
              type: 'hero',
              properties: { title: 'Resolved title', color: '#000000' },
              children: [],
            },
          },
          regions: [{ name: 'main', blocks: ['hero'] }],
        },
        translationReferences: {
          blocks: {
            hero: {
              properties: { title: 't:block.title' },
            },
          },
        },
        template: {
          url: 'https://example.test',
          name: 'index',
          sources: 'encrypted',
        },
      },
    });

    const canonical = canonicalizePage({
      blocks: {
        hero: {
          id: 'hero',
          type: 'hero',
          properties: { title: 'Resolved title', color: '#ffffff' },
          children: [],
        },
      },
      regions: [{ name: 'main', blocks: ['hero'] }],
    });

    expect(canonical.blocks.hero.properties).toEqual({
      title: 't:block.title',
      color: '#ffffff',
    });
  });
});

describe('unsupported page detection', () => {
  function makeEditor() {
    let handler: any;
    const editor = {
      preview: {
        onReady: (callback: Function) => callback(),
        onMessage: (_event: string, callback: Function) => {
          handler = callback;
        },
      },
      engine: {
        getBlocksManager: () => ({
          has: vi.fn(() => false),
          unregister: vi.fn(),
          register: vi.fn(),
        }),
        getBlockSchema: vi.fn(() => undefined),
        setPage: vi.fn(),
        getBlockById: vi.fn(),
      },
      ui: {
        openModal: vi.fn(),
      },
    } as any;

    return { editor, dispatch: (pageData: any) => handler({ pageData }) };
  }

  const templates = [{ template: 'index', label: 'Home', icon: '', previewUrl: 'https://example.test' }];

  it('opens the modal when the page template is not registered', () => {
    const state = makeState({ templates, previewLoading: true });
    const { editor, dispatch } = makeEditor();

    setupPageDataHandler(editor, state);
    dispatch({
      content: { blocks: {}, regions: [] },
      template: { url: 'https://example.test/login', name: 'shop-customer-session-index', sources: '' },
    });

    expect(state.unsupportedPage).toBe('missing-template');
    expect(state.previewLoading).toBe(false);
    expect(editor.ui.openModal).toHaveBeenCalledWith('unsupported-page');
  });

  it('clears the unsupported flag when a registered template loads', () => {
    const state = makeState({ templates, unsupportedPage: 'load-failed' });
    const { editor, dispatch } = makeEditor();

    setupPageDataHandler(editor, state);
    dispatch({
      content: { blocks: {}, regions: [] },
      template: { url: 'https://example.test', name: 'index', sources: '' },
    });

    expect(state.unsupportedPage).toBeNull();
    expect(editor.ui.openModal).not.toHaveBeenCalled();
  });
});
