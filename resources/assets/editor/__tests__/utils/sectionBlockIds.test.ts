import { describe, expect, it } from 'vitest';
import { sectionRowId } from '../../utils/sectionBlockIds';

describe('sectionRowId', () => {
  it('reads the row a section is stored as', () => {
    expect(sectionRowId('section-7')).toBe(7);
  });

  it('reads the row from an entry nested inside the section', () => {
    expect(sectionRowId('section-7.images.1')).toBe(7);
    expect(sectionRowId('section-12.links.0.items.3')).toBe(12);
  });

  it('answers nothing for a block that names no stored section', () => {
    expect(sectionRowId('hero')).toBeNull();
    expect(sectionRowId('section-')).toBeNull();
    expect(sectionRowId('section-abc')).toBeNull();
    expect(sectionRowId(null)).toBeNull();
    expect(sectionRowId(undefined)).toBeNull();
  });
});
