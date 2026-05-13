import { beforeEach, describe, expect, it, vi } from 'vitest';
import {
  initialChannelFromUrl,
  initialLocaleFromUrl,
  loadTemplateFromUrl,
  resolvePreviewUrl,
} from '../../../craftile/features/urlState';

const channels = [
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
    default_locale: 'en',
    locales: [{ code: 'en', name: 'English', logo_url: '' }],
  },
];

describe('editor url state', () => {
  beforeEach(() => {
    delete (window as any).location;
    (window as any).location = new URL('http://localhost:3000/admin/visual/editor/fake-theme');
  });

  it('initializes channel and locale from valid url params', () => {
    (window as any).location = new URL('http://localhost:3000/admin/visual/editor/fake-theme?channel=default&locale=fr');

    const channel = initialChannelFromUrl(channels, 'mobile');
    const locale = initialLocaleFromUrl(channels, channel, 'en');

    expect(channel).toBe('default');
    expect(locale).toBe('fr');
  });

  it('ignores invalid channel and locale url params', () => {
    (window as any).location = new URL('http://localhost:3000/admin/visual/editor/fake-theme?channel=unknown&locale=de');

    const channel = initialChannelFromUrl(channels, 'mobile');
    const locale = initialLocaleFromUrl(channels, channel, 'en');

    expect(channel).toBe('mobile');
    expect(locale).toBe('en');
  });

  it('uses same-origin previewUrl overrides', () => {
    (window as any).location = new URL(
      'http://localhost:3000/admin/visual/editor/fake-theme?previewUrl=%2Fcms%2Fabout-us'
    );

    expect(resolvePreviewUrl('http://localhost:3000/cms/default')).toBe('http://localhost:3000/cms/about-us');
  });

  it('ignores cross-origin previewUrl overrides', () => {
    (window as any).location = new URL(
      'http://localhost:3000/admin/visual/editor/fake-theme?previewUrl=https%3A%2F%2Fexample.com%2Fcms%2Fabout-us'
    );

    expect(resolvePreviewUrl('http://localhost:3000/cms/default')).toBe('http://localhost:3000/cms/default');
  });

  it('loads custom page templates with preview override and _template', () => {
    (window as any).location = new URL(
      'http://localhost:3000/admin/visual/editor/fake-theme?template=page.landing&previewUrl=%2Fcms%2Fabout-us'
    );

    const loadUrl = vi.fn();
    const editor = { preview: { loadUrl } } as any;

    loadTemplateFromUrl(
      editor,
      {
        channel: 'default',
        locale: 'en',
        theme: { code: 'fake-theme' },
      } as any,
      {
        storefrontUrl: 'http://localhost:3000',
        templates: [
          {
            template: 'page.landing',
            previewUrl: 'http://localhost:3000/cms/default',
            supportsVariants: false,
            type: 'page',
          },
        ],
      } as any
    );

    const loaded = new URL(loadUrl.mock.calls[0][0]);

    expect(loaded.href).toContain('/cms/about-us');
    expect(loaded.searchParams.get('_designMode')).toBe('fake-theme');
    expect(loaded.searchParams.get('channel')).toBe('default');
    expect(loaded.searchParams.get('locale')).toBe('en');
    expect(loaded.searchParams.get('_template')).toBe('page.landing');
  });
});
