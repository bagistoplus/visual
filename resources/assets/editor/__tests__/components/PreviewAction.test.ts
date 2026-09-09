import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import PreviewAction from '../../components/PreviewAction.vue';
import { createState } from '../../state';

const { CRAFTILE_EDITOR } = vi.hoisted(() => ({
  CRAFTILE_EDITOR: Symbol('CRAFTILE_EDITOR'),
}));

vi.mock('../../craftile/plugin', () => ({
  CRAFTILE_EDITOR,
}));

function mountPreviewAction({ pageDataUrl, previewUrl }: { pageDataUrl?: string; previewUrl: string }) {
  const state = createState({ theme: { code: 'fake-theme' } as any });

  if (pageDataUrl) {
    state.pageData = { url: pageDataUrl } as any;
  }

  const editor = { preview: { state: { previewUrl } } };

  const wrapper = mount(PreviewAction, {
    global: {
      provide: { [CRAFTILE_EDITOR]: editor },
      stubs: {
        Button: { template: '<button type="button" @click="$emit(\'click\')"><slot /></button>' },
      },
    },
  });

  return wrapper;
}

describe('PreviewAction', () => {
  let open: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    open = vi.fn();
    vi.stubGlobal('open', open);
  });

  it('swaps design mode for draft preview and drops the partial render key', async () => {
    const wrapper = mountPreviewAction({
      previewUrl: 'https://example.test/cms/about?_designMode=fake-theme&_visual_render=abc123',
    });

    await wrapper.find('button').trigger('click');

    const url = new URL(open.mock.calls[0][0]);

    expect(url.searchParams.get('_draftPreview')).toBe('fake-theme');
    expect(url.searchParams.has('_designMode')).toBe(false);
    expect(url.searchParams.has('_visual_render')).toBe(false);
    expect(open.mock.calls[0][1]).toBe('_blank');
  });

  it('keeps the channel, locale and template variant', async () => {
    const wrapper = mountPreviewAction({
      previewUrl:
        'https://example.test/products/mug?_designMode=fake-theme&channel=indie&locale=fr&_template=product.gift-box',
    });

    await wrapper.find('button').trigger('click');

    const url = new URL(open.mock.calls[0][0]);

    expect(url.pathname).toBe('/products/mug');
    expect(url.searchParams.get('channel')).toBe('indie');
    expect(url.searchParams.get('locale')).toBe('fr');
    expect(url.searchParams.get('_template')).toBe('product.gift-box');
  });

  it('prefers the rendered page url over the stale preview url', async () => {
    const wrapper = mountPreviewAction({
      pageDataUrl: 'https://example.test/cart?_designMode=fake-theme',
      previewUrl: 'https://example.test/?_designMode=fake-theme',
    });

    await wrapper.find('button').trigger('click');

    expect(new URL(open.mock.calls[0][0]).pathname).toBe('/cart');
  });

  it('falls back to the active theme when the url carries no design mode', async () => {
    const wrapper = mountPreviewAction({ previewUrl: 'https://example.test/' });

    await wrapper.find('button').trigger('click');

    expect(new URL(open.mock.calls[0][0]).searchParams.get('_draftPreview')).toBe('fake-theme');
  });

  it('does nothing when there is no preview url', async () => {
    const wrapper = mountPreviewAction({ previewUrl: '' });

    await wrapper.find('button').trigger('click');

    expect(open).not.toHaveBeenCalled();
  });
});
