import { describe, expect, it } from 'vitest';
import { holdsBlockOfType } from '../../utils/singletonSections';

describe('holdsBlockOfType', () => {
  const page = {
    'section-7': { type: 'image_carousel' },
    'section-9': { type: 'footer_links' },
  };

  it('says so when the page already holds one', () => {
    expect(holdsBlockOfType(page, 'footer_links')).toBe(true);
  });

  it('says so when it does not', () => {
    expect(holdsBlockOfType(page, 'services_content')).toBe(false);
  });

  it('reads a page that holds nothing at all', () => {
    expect(holdsBlockOfType({}, 'footer_links')).toBe(false);
    expect(holdsBlockOfType(undefined, 'footer_links')).toBe(false);
  });
});
