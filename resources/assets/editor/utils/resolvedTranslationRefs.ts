import type { Block, BlockSchema, Page, PropertyField, UpdatesEvent } from '@craftile/types';

type ResolvedTranslationRef = {
  raw: any;
  resolved: any;
};

type BlocksProperties = Record<
  string,
  {
    properties?: Block['properties'];
  }
>;

const refs = new Map<string, Map<string, ResolvedTranslationRef>>();

export function isTranslationReference(value: unknown): boolean {
  return typeof value === 'string' && value.startsWith('t:') && value !== 't:';
}

export function clearResolvedTranslationRefs(): void {
  refs.clear();
}

export function recordResolvedTranslationRefs(
  currentBlocks: BlocksProperties,
  resolvedBlocks: Record<string, Block>,
  getBlockSchema: (type: string) => BlockSchema | undefined
): void {
  for (const [blockId, resolvedBlock] of Object.entries(resolvedBlocks)) {
    const schema = getBlockSchema(resolvedBlock.type);
    const currentBlock = currentBlocks[blockId];
    const currentProperties = currentBlock?.properties || {};
    const resolvedProperties = resolvedBlock.properties || {};

    if (!schema) {
      continue;
    }

    for (const property of schema.properties) {
      if (!property.localized) {
        continue;
      }

      const propertyId = property.id;
      const resolvedValue = resolvedProperties[propertyId];
      const currentValue = currentProperties[propertyId];
      const existing = getRef(blockId, propertyId);
      const rawValue = existing?.raw ?? currentValue;
      const ref = makeResolvedTranslationRef(rawValue, resolvedValue, property);

      if (!ref) {
        continue;
      }

      setRef(blockId, propertyId, ref);
    }
  }
}

export function canonicalizeUpdates(updates: UpdatesEvent): UpdatesEvent {
  return {
    ...updates,
    blocks: canonicalizeBlocks(updates.blocks),
  };
}

export function canonicalizePage<T extends Page | null | undefined>(page: T): T {
  if (!page) {
    return page;
  }

  return {
    ...page,
    blocks: canonicalizeBlocks(page.blocks),
  } as T;
}

export function canonicalizeBlocks(blocks: Record<string, Block>): Record<string, Block> {
  const canonicalBlocks: Record<string, Block> = {};

  for (const [blockId, block] of Object.entries(blocks)) {
    canonicalBlocks[blockId] = canonicalizeBlock(block);
  }

  return canonicalBlocks;
}

function canonicalizeBlock(block: Block): Block {
  const blockRefs = refs.get(block.id);

  if (!blockRefs) {
    return structuredClone(block);
  }

  const canonicalBlock = structuredClone(block);

  for (const [propertyId, ref] of blockRefs.entries()) {
    if (!canonicalBlock.properties[propertyId]) {
      continue;
    }

    const canonicalValue = canonicalizeValue(canonicalBlock.properties[propertyId], ref);

    if (canonicalValue !== undefined) {
      canonicalBlock.properties[propertyId] = canonicalValue;
    }
  }

  return canonicalBlock;
}

function makeResolvedTranslationRef(
  rawValue: unknown,
  resolvedValue: unknown,
  property: Pick<PropertyField, 'responsive'>
): ResolvedTranslationRef | null {
  if (isTranslationReference(rawValue) && rawValue !== resolvedValue) {
    return {
      raw: rawValue,
      resolved: resolvedValue,
    };
  }

  if (property.responsive === true && isPlainObject(rawValue) && isPlainObject(resolvedValue)) {
    const raw: Record<string, unknown> = {};
    const resolved: Record<string, unknown> = {};

    for (const [key, item] of Object.entries(rawValue)) {
      const resolvedItem = resolvedValue[key];

      if (isTranslationReference(item) && item !== resolvedItem) {
        raw[key] = item;
        resolved[key] = resolvedItem;
      }
    }

    if (Object.keys(raw).length > 0) {
      return { raw, resolved };
    }
  }

  return null;
}

function canonicalizeValue(value: unknown, ref: ResolvedTranslationRef): unknown {
  if (isPlainObject(ref.raw) && isPlainObject(ref.resolved) && isPlainObject(value)) {
    const canonicalValue = structuredClone(value);

    for (const [key, resolvedItem] of Object.entries(ref.resolved)) {
      if (canonicalValue[key] === resolvedItem) {
        canonicalValue[key] = ref.raw[key];
      }
    }

    return canonicalValue;
  }

  return value === ref.resolved ? ref.raw : undefined;
}

function getRef(blockId: string, propertyId: string): ResolvedTranslationRef | undefined {
  return refs.get(blockId)?.get(propertyId);
}

function setRef(blockId: string, propertyId: string, ref: ResolvedTranslationRef): void {
  if (!refs.has(blockId)) {
    refs.set(blockId, new Map());
  }

  refs.get(blockId)!.set(propertyId, ref);
}

function isPlainObject(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value);
}
