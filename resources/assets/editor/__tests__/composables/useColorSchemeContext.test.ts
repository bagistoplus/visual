import { describe, it, expect, beforeEach, vi } from 'vitest';
import type { Block, BlockSchema } from '@craftile/types';
import type { Theme } from '../../types';

vi.mock('../../craftile/plugin', () => ({
  CRAFTILE_EDITOR: Symbol('CRAFTILE_EDITOR'),
  default: () => () => {},
}));

vi.mock('../../composables/useCraftileEditor', () => ({
  useCraftileEditor: () => undefined,
}));

vi.mock('../../state', () => ({
  useState: () => ({ theme: { value: null } }),
}));

type BlockMap = Record<string, Block>;
type SchemaMap = Record<string, BlockSchema>;

function makeGetters(blocks: BlockMap, schemas: SchemaMap) {
  return {
    getBlock: (id: string) => blocks[id],
    getBlockSchema: (type: string) => schemas[type],
  };
}

function makeTheme(overrides: Partial<Theme> = {}): Theme {
  return {
    name: 'Test',
    code: 'test',
    version: '1.0.0',
    settings: {},
    settingsSchema: [],
    ...overrides,
  };
}

async function loadComposableModule() {
  return await import('../../composables/useColorSchemeContext');
}

describe('findContextualSchemeId', () => {
  it('returns the value of the selected block when it has a color_scheme property', async () => {
    const { findContextualSchemeId } = await loadComposableModule();
    const blocks: BlockMap = {
      a: {
        type: 'section',
        id: 'a',
        properties: { my_scheme: 'dark' },
        children: [],
      },
    };
    const schemas: SchemaMap = {
      section: {
        type: 'section',
        properties: [{ id: 'my_scheme', type: 'color_scheme' }],
      },
    };
    const { getBlock, getBlockSchema } = makeGetters(blocks, schemas);

    expect(findContextualSchemeId(blocks.a, getBlock, getBlockSchema)).toBe('dark');
  });

  it('walks up to nearest ancestor when child has no color_scheme property', async () => {
    const { findContextualSchemeId } = await loadComposableModule();
    const blocks: BlockMap = {
      parent: {
        type: 'section',
        id: 'parent',
        properties: { scheme: 'dark' },
        children: ['child'],
      },
      child: {
        type: 'button',
        id: 'child',
        parentId: 'parent',
        properties: {},
        children: [],
      },
    };
    const schemas: SchemaMap = {
      section: {
        type: 'section',
        properties: [{ id: 'scheme', type: 'color_scheme' }],
      },
      button: {
        type: 'button',
        properties: [{ id: 'label', type: 'text' }],
      },
    };
    const { getBlock, getBlockSchema } = makeGetters(blocks, schemas);

    expect(findContextualSchemeId(blocks.child, getBlock, getBlockSchema)).toBe('dark');
  });

  it('skips ancestors where the color_scheme property is empty', async () => {
    const { findContextualSchemeId } = await loadComposableModule();
    const blocks: BlockMap = {
      grand: {
        type: 'page',
        id: 'grand',
        properties: { theme: 'high-contrast' },
        children: ['parent'],
      },
      parent: {
        type: 'section',
        id: 'parent',
        parentId: 'grand',
        properties: { scheme: '' },
        children: ['child'],
      },
      child: {
        type: 'button',
        id: 'child',
        parentId: 'parent',
        properties: {},
        children: [],
      },
    };
    const schemas: SchemaMap = {
      page: { type: 'page', properties: [{ id: 'theme', type: 'color_scheme' }] },
      section: { type: 'section', properties: [{ id: 'scheme', type: 'color_scheme' }] },
      button: { type: 'button', properties: [] },
    };
    const { getBlock, getBlockSchema } = makeGetters(blocks, schemas);

    expect(findContextualSchemeId(blocks.child, getBlock, getBlockSchema)).toBe('high-contrast');
  });

  it('returns null when no ancestor sets a color_scheme', async () => {
    const { findContextualSchemeId } = await loadComposableModule();
    const blocks: BlockMap = {
      a: { type: 'button', id: 'a', properties: {}, children: [] },
    };
    const schemas: SchemaMap = {
      button: { type: 'button', properties: [{ id: 'label', type: 'text' }] },
    };
    const { getBlock, getBlockSchema } = makeGetters(blocks, schemas);

    expect(findContextualSchemeId(blocks.a, getBlock, getBlockSchema)).toBeNull();
  });
});

describe('findColorSchemeGroup', () => {
  it('returns the group setting and its schemes', async () => {
    const { findColorSchemeGroup } = await loadComposableModule();
    const theme = makeTheme({
      settingsSchema: [
        { name: 'Colors', settings: [{ id: 'palette', type: 'color_scheme_group', label: 'Palette', component: 'c' }] },
      ],
      settings: { palette: { light: { primary: '#fff' } } },
    });

    expect(findColorSchemeGroup(theme)).toEqual({
      id: 'palette',
      schemes: { light: { primary: '#fff' } },
    });
  });

  it('returns null when no color_scheme_group setting exists', async () => {
    const { findColorSchemeGroup } = await loadComposableModule();
    expect(findColorSchemeGroup(makeTheme())).toBeNull();
  });
});

describe('resolveActiveSchemeId', () => {
  const groupSchema = {
    id: 'palette',
    type: 'color_scheme_group',
    label: 'Palette',
    component: 'c',
  };

  it('selected block scheme wins over default and theme group', async () => {
    const { resolveActiveSchemeId } = await loadComposableModule();
    const blocks: BlockMap = {
      a: { type: 'section', id: 'a', properties: { scheme: 'dark' }, children: [] },
    };
    const schemas: SchemaMap = {
      section: { type: 'section', properties: [{ id: 'scheme', type: 'color_scheme' }] },
    };
    const theme = makeTheme({
      settingsSchema: [{ name: 'Colors', settings: [groupSchema] }],
      settings: { palette: { light: {}, dark: {} }, default_scheme: 'light' },
    });
    const { getBlock, getBlockSchema } = makeGetters(blocks, schemas);

    expect(resolveActiveSchemeId(blocks.a, getBlock, getBlockSchema, theme)).toBe('dark');
  });

  it('falls back to theme default_scheme when no contextual scheme', async () => {
    const { resolveActiveSchemeId } = await loadComposableModule();
    const theme = makeTheme({
      settingsSchema: [{ name: 'Colors', settings: [groupSchema] }],
      settings: { palette: { light: {}, dark: {} }, default_scheme: 'dark' },
    });

    expect(
      resolveActiveSchemeId(
        undefined,
        () => undefined,
        () => undefined,
        theme
      )
    ).toBe('dark');
  });

  it('falls back to first scheme key when default_scheme is missing', async () => {
    const { resolveActiveSchemeId } = await loadComposableModule();
    const theme = makeTheme({
      settingsSchema: [{ name: 'Colors', settings: [groupSchema] }],
      settings: { palette: { light: {}, dark: {} } },
    });

    expect(
      resolveActiveSchemeId(
        undefined,
        () => undefined,
        () => undefined,
        theme
      )
    ).toBe('light');
  });
});

describe('useColorSchemeContext wiring', () => {
  beforeEach(() => {
    vi.resetModules();
  });

  it('returns the canonical token options and null defaults when there is no editor or theme', async () => {
    vi.doMock('../../state', () => ({
      useState: () => ({ theme: { value: null } }),
    }));
    vi.doMock('../../composables/useCraftileEditor', () => ({
      useCraftileEditor: () => undefined,
    }));

    const { useColorSchemeContext, COLOR_TOKEN_OPTIONS } = await loadComposableModule();
    const ctx = useColorSchemeContext();

    expect(ctx.colorTokenOptions).toEqual(COLOR_TOKEN_OPTIONS);
    expect(ctx.activeSchemeId.value).toBeNull();
    expect(ctx.activeSchemeTokens.value).toBeNull();
    expect(ctx.resolveTokenColor('primary')).toBeNull();
  });

  it('resolves swatches via default_scheme and returns null for missing tokens', async () => {
    const theme = makeTheme({
      settingsSchema: [
        { name: 'Colors', settings: [{ id: 'palette', type: 'color_scheme_group', label: 'Palette', component: 'c' }] },
      ],
      settings: {
        palette: { light: { primary: '#ff0000' } },
        default_scheme: 'light',
      },
    });

    vi.doMock('../../state', () => ({
      useState: () => ({ theme: { value: theme } }),
    }));
    vi.doMock('../../composables/useCraftileEditor', () => ({
      useCraftileEditor: () => ({
        getActiveBlock: () => undefined,
        events: { on: () => () => {} },
        engine: {
          on: () => () => {},
          getBlockById: () => undefined,
          getBlockSchema: () => undefined,
        },
      }),
    }));

    const { useColorSchemeContext } = await loadComposableModule();
    const ctx = useColorSchemeContext();

    expect(ctx.activeSchemeId.value).toBe('light');
    expect(ctx.resolveTokenColor('primary')).toBe('#ff0000');
    // Missing token returns null without changing resolved scheme
    expect(ctx.resolveTokenColor('danger')).toBeNull();
    expect(ctx.activeSchemeId.value).toBe('light');
  });

  it('repaints when an ancestor scheme changes for a child without its own color_scheme', async () => {
    const parent = {
      type: 'section',
      id: 'parent',
      properties: { scheme: 'light' },
      children: ['child'],
    };
    const child = {
      type: 'button',
      id: 'child',
      parentId: 'parent',
      properties: {},
      children: [] as string[],
    };
    const theme = makeTheme({
      settingsSchema: [
        { name: 'Colors', settings: [{ id: 'palette', type: 'color_scheme_group', label: 'Palette', component: 'c' }] },
      ],
      settings: {
        palette: {
          light: { primary: '#ff0000' },
          dark: { primary: '#0000ff' },
        },
      },
    });
    const handlers = new Map<string, ((data: unknown) => void)[]>();
    const captureOn = (event: string, fn: (data: unknown) => void) => {
      const list = handlers.get(event) ?? [];
      list.push(fn);
      handlers.set(event, list);
      return () => {};
    };
    const schemas: Record<string, BlockSchema> = {
      section: { type: 'section', properties: [{ id: 'scheme', type: 'color_scheme' }] },
      button: { type: 'button', properties: [] },
    };
    const byId: Record<string, typeof parent | typeof child> = { parent, child };

    vi.doMock('../../state', () => ({
      useState: () => ({ theme: { value: theme } }),
    }));
    vi.doMock('../../composables/useCraftileEditor', () => ({
      useCraftileEditor: () => ({
        getActiveBlock: () => child,
        events: { on: captureOn },
        engine: {
          on: captureOn,
          getBlockById: (id: string) => byId[id],
          getBlockSchema: (type: string) => schemas[type],
        },
      }),
    }));

    const { useColorSchemeContext } = await loadComposableModule();
    const ctx = useColorSchemeContext();

    expect(ctx.activeSchemeId.value).toBe('light');
    expect(ctx.resolveTokenColor('primary')).toBe('#ff0000');

    parent.properties.scheme = 'dark';
    handlers
      .get('block:property:set')
      ?.forEach((fn) => fn({ blockId: 'parent', key: 'scheme', value: 'dark', oldValue: 'light' }));

    expect(ctx.activeSchemeId.value).toBe('dark');
    expect(ctx.resolveTokenColor('primary')).toBe('#0000ff');
  });

  it('repaints when theme default_scheme changes (no contextual block scheme)', async () => {
    const { reactive, nextTick } = await import('vue');
    const theme = reactive(
      makeTheme({
        settingsSchema: [
          {
            name: 'Colors',
            settings: [{ id: 'palette', type: 'color_scheme_group', label: 'Palette', component: 'c' }],
          },
        ],
        settings: {
          palette: {
            light: { primary: '#ff0000' },
            dark: { primary: '#0000ff' },
          },
          default_scheme: 'light',
        },
      })
    );

    vi.doMock('../../state', () => ({
      useState: () => ({ theme: { value: theme } }),
    }));
    vi.doMock('../../composables/useCraftileEditor', () => ({
      useCraftileEditor: () => ({
        getActiveBlock: () => undefined,
        events: { on: () => () => {} },
        engine: {
          on: () => () => {},
          getBlockById: () => undefined,
          getBlockSchema: () => undefined,
        },
      }),
    }));

    const { useColorSchemeContext } = await loadComposableModule();
    const ctx = useColorSchemeContext();

    expect(ctx.activeSchemeId.value).toBe('light');
    expect(ctx.resolveTokenColor('primary')).toBe('#ff0000');

    theme.settings.default_scheme = 'dark';
    await nextTick();

    expect(ctx.activeSchemeId.value).toBe('dark');
    expect(ctx.resolveTokenColor('primary')).toBe('#0000ff');
  });

  it('reports unavailable when active scheme id points to a missing scheme', async () => {
    const theme = makeTheme({
      settingsSchema: [
        { name: 'Colors', settings: [{ id: 'palette', type: 'color_scheme_group', label: 'Palette', component: 'c' }] },
      ],
      settings: {
        palette: { light: { primary: '#ff0000' } },
        default_scheme: 'missing',
      },
    });

    vi.doMock('../../state', () => ({
      useState: () => ({ theme: { value: theme } }),
    }));
    vi.doMock('../../composables/useCraftileEditor', () => ({
      useCraftileEditor: () => ({
        getActiveBlock: () => undefined,
        events: { on: () => () => {} },
        engine: {
          on: () => () => {},
          getBlockById: () => undefined,
          getBlockSchema: () => undefined,
        },
      }),
    }));

    const { useColorSchemeContext } = await loadComposableModule();
    const ctx = useColorSchemeContext();

    expect(ctx.activeSchemeId.value).toBe('missing');
    expect(ctx.activeSchemeTokens.value).toBeNull();
    expect(ctx.resolveTokenColor('primary')).toBeNull();
  });
});
