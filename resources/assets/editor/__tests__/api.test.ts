import { afterEach, describe, expect, it, vi } from 'vitest';

import { persistUpdates, publishTheme } from '../api';
import { createState } from '../state';
import {
  clearResolvedTranslationRefs,
  recordResolvedTranslationRefs,
} from '../utils/resolvedTranslationRefs';

const getHeroSchema = () => ({
  type: 'hero',
  properties: [{ id: 'title', localized: true }],
} as any);

afterEach(() => {
  clearResolvedTranslationRefs();
  vi.restoreAllMocks();
});

function setupEditorState() {
  (window as any).editorConfig = {
    defaultChannel: 'default',
    editorLocale: 'en',
    routes: {
      persistUpdates: '/persist-updates',
      publishTheme: '/publish-theme',
    },
  };

  const state = createState({
    channel: 'default',
    locale: 'en',
    theme: {
      code: 'theme',
      name: 'Theme',
      version: '1.0.0',
      settings: {},
      settingsSchema: [],
    },
  });

  state.pageData = {
    url: 'https://example.test',
    template: 'index',
    sources: 'encrypted',
  };
}

describe('editor api translation ref canonicalization', () => {
  it('canonicalizes resolved translation refs before persisting updates', async () => {
    setupEditorState();
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
      getHeroSchema
    );
    const fetchMock = vi
      .spyOn(globalThis, 'fetch')
      .mockResolvedValue(new Response('ok', { status: 200 }));

    await persistUpdates({
      blocks: {
        hero: {
          id: 'hero',
          type: 'hero',
          properties: { title: 'Resolved title' },
          children: [],
        },
      },
      regions: [],
      changes: {
        added: [],
        updated: ['hero'],
        removed: [],
        moved: {},
      },
    }).execute();

    const body = JSON.parse(fetchMock.mock.calls[0][1]!.body as string);
    expect(body.updates.blocks.hero.properties.title).toBe('t:block.title');
  });

  it('canonicalizes resolved translation refs before publishing a full page', async () => {
    setupEditorState();
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
      getHeroSchema
    );
    const fetchMock = vi
      .spyOn(globalThis, 'fetch')
      .mockResolvedValue(new Response(JSON.stringify({ success: true }), { status: 200 }));

    await publishTheme({
      blocks: {
        hero: {
          id: 'hero',
          type: 'hero',
          properties: { title: 'Resolved title' },
          children: [],
        },
      },
      regions: [{ name: 'main', blocks: ['hero'] }],
    }).execute();

    const body = JSON.parse(fetchMock.mock.calls[0][1]!.body as string);
    expect(body.page.blocks.hero.properties.title).toBe('t:block.title');
  });
});
