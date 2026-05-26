import type { Block, BlockSchema } from '@craftile/types';
import { onScopeDispose } from 'vue';
import { useState } from '../state';
import { useCraftileEditor } from './useCraftileEditor';
import type { Theme } from '../types';

export const COLOR_TOKEN_OPTIONS = [
  'primary',
  'secondary',
  'accent',
  'neutral',
  'success',
  'warning',
  'danger',
  'info',
] as const;

export type ColorTokenOption = (typeof COLOR_TOKEN_OPTIONS)[number];

// Must stay in sync with ColorTokenValue::EMPTY_VALUE on the PHP side.
export const COLOR_TOKEN_EMPTY_VALUE = '__none__';

type GetBlock = (id: string) => Block | undefined;
type GetBlockSchema = (type: string) => BlockSchema | undefined;

export function findContextualSchemeId(
  startBlock: Block | undefined,
  getBlock: GetBlock,
  getBlockSchema: GetBlockSchema
): string | null {
  let current: Block | undefined = startBlock;

  while (current) {
    const schema = getBlockSchema(current.type);
    if (schema && Array.isArray(schema.properties)) {
      const prop = schema.properties.find((p) => p.type === 'color_scheme');
      if (prop) {
        const value = current.properties?.[prop.id];
        if (typeof value === 'string' && value !== '') {
          return value;
        }
      }
    }

    if (!current.parentId) {
      break;
    }
    current = getBlock(current.parentId);
  }

  return null;
}

export function findColorSchemeGroup(
  theme: Theme | null
): { id: string; schemes: Record<string, Record<string, string>> } | null {
  if (!theme) {
    return null;
  }

  const setting = theme.settingsSchema.flatMap((group) => group.settings).find((s) => s.type === 'color_scheme_group');

  if (!setting) {
    return null;
  }

  const schemes = (theme.settings?.[setting.id] as Record<string, Record<string, string>>) || {};

  return { id: setting.id, schemes };
}

export function resolveActiveSchemeId(
  activeBlock: Block | undefined,
  getBlock: GetBlock,
  getBlockSchema: GetBlockSchema,
  theme: Theme | null
): string | null {
  const contextual = findContextualSchemeId(activeBlock, getBlock, getBlockSchema);
  if (contextual) {
    return contextual;
  }

  const defaultScheme = theme?.settings?.default_scheme;
  if (typeof defaultScheme === 'string' && defaultScheme !== '') {
    return defaultScheme;
  }

  const group = findColorSchemeGroup(theme);
  if (!group) {
    return null;
  }

  const firstKey = Object.keys(group.schemes)[0];
  return firstKey || null;
}

export function useColorSchemeContext() {
  const editor = useCraftileEditor();
  const { theme } = useState();

  const tick = ref(0);
  const bump = () => {
    tick.value++;
  };

  if (editor) {
    const offs = [
      editor.events.on('ui:block:select', bump),
      editor.events.on('ui:block:clear-selection', bump),
      editor.engine.on('block:property:set', bump),
      editor.engine.on('block:update', bump),
      editor.engine.on('page:set', bump),
    ];

    onScopeDispose(() => {
      offs.forEach((off) => off());
    });
  }

  const activeSchemeId = computed<string | null>(() => {
    // Touch tick so engine/UI events force a recompute
    void tick.value;

    if (!editor) {
      return null;
    }

    return resolveActiveSchemeId(
      editor.getActiveBlock(),
      (id) => editor.engine.getBlockById(id),
      (type) => editor.engine.getBlockSchema(type),
      theme.value
    );
  });

  const activeSchemeTokens = computed<Record<string, string> | null>(() => {
    void tick.value;

    if (!theme.value) {
      return null;
    }

    const group = findColorSchemeGroup(theme.value);
    if (!group) {
      return null;
    }

    const id = activeSchemeId.value;
    if (!id) {
      return null;
    }

    return group.schemes[id] ?? null;
  });

  function resolveTokenColor(token: string): string | null {
    const tokens = activeSchemeTokens.value;
    if (!tokens) {
      return null;
    }

    const value = tokens[token];
    return typeof value === 'string' && value !== '' ? value : null;
  }

  return {
    activeSchemeId,
    activeSchemeTokens,
    colorTokenOptions: COLOR_TOKEN_OPTIONS,
    resolveTokenColor,
  };
}
