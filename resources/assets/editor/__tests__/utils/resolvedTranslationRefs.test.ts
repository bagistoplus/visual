import { afterEach, describe, expect, it } from 'vitest';
import type { Block, Page, UpdatesEvent } from '@craftile/types';
import {
  canonicalizePage,
  canonicalizeUpdates,
  clearResolvedTranslationRefs,
  isTranslationReference,
  recordResolvedTranslationRefs,
} from '../../utils/resolvedTranslationRefs';

afterEach(() => {
  clearResolvedTranslationRefs();
});

function block(id: string, properties: Record<string, any>): Block {
  return {
    id,
    type: 'hero',
    properties,
    children: [],
  };
}

function schema(properties: Record<string, any>[]) {
  return () => ({
    type: 'hero',
    properties,
  } as any);
}

describe('resolved translation refs', () => {
  it('detects translation references', () => {
    expect(isTranslationReference('t:block.title')).toBe(true);
    expect(isTranslationReference('t:')).toBe(false);
    expect(isTranslationReference('plain')).toBe(false);
  });

  it('canonicalizes unchanged resolved values in update payloads back to their raw references', () => {
    recordResolvedTranslationRefs(
      {
        hero: block('hero', { title: 't:block.title' }),
      },
      {
        hero: block('hero', { title: 'Resolved title' }),
      },
      schema([{ id: 'title', localized: true }])
    );

    const updates: UpdatesEvent = {
      blocks: {
        hero: block('hero', { title: 'Resolved title' }),
      },
      regions: [],
      changes: {
        added: [],
        updated: ['hero'],
        removed: [],
        moved: {},
      },
    };

    expect(canonicalizeUpdates(updates).blocks.hero.properties.title).toBe('t:block.title');
  });

  it('keeps user-edited values when they differ from the resolved sidecar value', () => {
    recordResolvedTranslationRefs(
      {
        hero: block('hero', { title: 't:block.title' }),
      },
      {
        hero: block('hero', { title: 'Resolved title' }),
      },
      schema([{ id: 'title', localized: true }])
    );

    const updates: UpdatesEvent = {
      blocks: {
        hero: block('hero', { title: 'Custom title' }),
      },
      regions: [],
      changes: {
        added: [],
        updated: ['hero'],
        removed: [],
        moved: {},
      },
    };

    expect(canonicalizeUpdates(updates).blocks.hero.properties.title).toBe('Custom title');
  });

  it('does not restore a stale translation reference after persisting a literal override', () => {
    const localizedSchema = schema([{ id: 'label', localized: true }, { id: 'width', localized: false }]);

    recordResolvedTranslationRefs(
      {
        hero: block('hero', { label: 't:block.label', width: 'dropdown' }),
      },
      {
        hero: block('hero', { label: 'Mega menu', width: 'dropdown' }),
      },
      localizedSchema
    );

    const editedBlock = block('hero', { label: 'Shup', width: 'dropdown' });

    expect(
      canonicalizeUpdates({
        blocks: { hero: editedBlock },
        regions: [],
        changes: {
          added: [],
          updated: ['hero'],
          removed: [],
          moved: {},
        },
      }).blocks.hero.properties.label
    ).toBe('Shup');

    recordResolvedTranslationRefs(
      {
        hero: editedBlock,
      },
      {
        hero: block('hero', { label: 'Shup', width: 'dropdown' }),
      },
      localizedSchema
    );

    const unrelatedUpdate: UpdatesEvent = {
      blocks: {
        hero: block('hero', { label: 'Shup', width: 'container' }),
      },
      regions: [],
      changes: {
        added: [],
        updated: ['hero'],
        removed: [],
        moved: {},
      },
    };

    expect(canonicalizeUpdates(unrelatedUpdate).blocks.hero.properties).toEqual({
      label: 'Shup',
      width: 'container',
    });
  });

  it('canonicalizes full page payloads', () => {
    recordResolvedTranslationRefs(
      {
        hero: block('hero', { title: { _default: 'desktop', desktop: 't:block.desktop', mobile: 't:block.mobile' } }),
      },
      {
        hero: block('hero', { title: { _default: 'desktop', desktop: 'Desktop title', mobile: 'Mobile title' } }),
      },
      schema([{ id: 'title', localized: true, responsive: true }])
    );

    const page: Page = {
      blocks: {
        hero: block('hero', { title: { _default: 'desktop', desktop: 'Desktop title', mobile: 'Mobile title' } }),
      },
      regions: [{ name: 'main', blocks: ['hero'] }],
    };

    expect(canonicalizePage(page).blocks.hero.properties.title).toEqual({
      _default: 'desktop',
      desktop: 't:block.desktop',
      mobile: 't:block.mobile',
    });
  });

  it('canonicalizes unchanged responsive breakpoints while preserving edited breakpoints', () => {
    recordResolvedTranslationRefs(
      {
        hero: block('hero', { title: { _default: 'desktop', desktop: 't:block.desktop', mobile: 't:block.mobile' } }),
      },
      {
        hero: block('hero', { title: { _default: 'desktop', desktop: 'Desktop title', mobile: 'Mobile title' } }),
      },
      schema([{ id: 'title', localized: true, responsive: true }])
    );

    const page: Page = {
      blocks: {
        hero: block('hero', { title: { _default: 'desktop', desktop: 'Custom desktop', mobile: 'Mobile title' } }),
      },
      regions: [{ name: 'main', blocks: ['hero'] }],
    };

    expect(canonicalizePage(page).blocks.hero.properties.title).toEqual({
      _default: 'desktop',
      desktop: 'Custom desktop',
      mobile: 't:block.mobile',
    });
  });

  it('does not restore stale responsive references after persisting an edited breakpoint', () => {
    const responsiveSchema = schema([{ id: 'title', localized: true, responsive: true }]);

    recordResolvedTranslationRefs(
      {
        hero: block('hero', { title: { _default: 'desktop', desktop: 't:block.desktop', mobile: 't:block.mobile' } }),
      },
      {
        hero: block('hero', { title: { _default: 'desktop', desktop: 'Desktop title', mobile: 'Mobile title' } }),
      },
      responsiveSchema
    );

    const editedTitle = { _default: 'desktop', desktop: 'Custom desktop', mobile: 'Mobile title' };

    recordResolvedTranslationRefs(
      {
        hero: block('hero', { title: editedTitle }),
      },
      {
        hero: block('hero', { title: editedTitle }),
      },
      responsiveSchema
    );

    const page: Page = {
      blocks: {
        hero: block('hero', { title: editedTitle }),
      },
      regions: [{ name: 'main', blocks: ['hero'] }],
    };

    expect(canonicalizePage(page).blocks.hero.properties.title).toEqual({
      _default: 'desktop',
      desktop: 'Custom desktop',
      mobile: 't:block.mobile',
    });
  });

  it('clears sidecar refs', () => {
    recordResolvedTranslationRefs(
      {
        hero: block('hero', { title: 't:block.title' }),
      },
      {
        hero: block('hero', { title: 'Resolved title' }),
      },
      schema([{ id: 'title', localized: true }])
    );

    clearResolvedTranslationRefs();

    const page: Page = {
      blocks: {
        hero: block('hero', { title: 'Resolved title' }),
      },
      regions: [{ name: 'main', blocks: ['hero'] }],
    };

    expect(canonicalizePage(page).blocks.hero.properties.title).toBe('Resolved title');
  });

  it('ignores non-localized schema properties even when values look like translation refs', () => {
    recordResolvedTranslationRefs(
      {
        hero: block('hero', { handle: 't:block.handle' }),
      },
      {
        hero: block('hero', { handle: 'Resolved handle' }),
      },
      schema([{ id: 'handle', localized: false }])
    );

    const page: Page = {
      blocks: {
        hero: block('hero', { handle: 'Resolved handle' }),
      },
      regions: [{ name: 'main', blocks: ['hero'] }],
    };

    expect(canonicalizePage(page).blocks.hero.properties.handle).toBe('Resolved handle');
  });
});
