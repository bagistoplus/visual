import { mount } from '@vue/test-utils';
import { describe, expect, it, vi, beforeEach } from 'vitest';
import { ref } from 'vue';

const { CRAFTILE_EDITOR } = vi.hoisted(() => ({
  CRAFTILE_EDITOR: Symbol('CRAFTILE_EDITOR'),
}));

vi.mock('../../craftile/plugin', () => ({ CRAFTILE_EDITOR }));

const { copySectionMedia, uploadSectionMedia, listSectionMedia } = vi.hoisted(() => ({
  copySectionMedia: vi.fn(),
  uploadSectionMedia: vi.fn(),
  listSectionMedia: vi.fn(),
}));

vi.mock('../../api', () => ({ copySectionMedia, uploadSectionMedia, listSectionMedia }));

const { files, load, remember } = vi.hoisted(() => ({
  files: { value: [] as any[] },
  load: vi.fn(),
  remember: vi.fn(),
}));

vi.mock('../../composables/useSectionMedia', () => ({
  useSectionMedia: () => ({
    files: ref(files.value),
    loading: ref(false),
    load,
    remember,
    find: (path: string) =>
      files.value.find((file) => file.path === String(path ?? '').replace(/^\/?storage\//, '')) ?? null,
  }),
}));

import SectionImagePicker from '../../components/SectionImagePicker.vue';

const LIBRARY = [
  { path: 'themes/default/sections/7/mine.webp', url: '/storage/mine.webp', section: 7 },
  { path: 'themes/default/sections/9/theirs.webp', url: '/storage/theirs.webp', section: 9 },
];

function fakeEditor(selectedBlockId: string | null = 'section-7') {
  return { ui: { state: { selectedBlockId }, toast: vi.fn() } };
}

function mountPicker(modelValue: string | null = null, editor: any = fakeEditor()) {
  return mount(SectionImagePicker, {
    props: { field: { id: 'image', type: 'image', label: 'Slide' }, modelValue },
    global: {
      provide: { [CRAFTILE_EDITOR]: editor },
      stubs: {
        ImagePreview: { props: ['image', 'selected'], template: '<button :data-path="image.path" />' },
        Button: { template: '<button><slot /></button>' },
      },
    },
  });
}

function requestOf(result: any) {
  const success: Array<(value: any) => void> = [];

  return {
    onSuccess: (callback: (value: any) => void) => success.push(callback),
    onError: () => {},
    execute: async () => success.forEach((callback) => callback(result)),
  };
}

async function openAndPick(wrapper: ReturnType<typeof mountPicker>, path: string) {
  (wrapper.vm as any).opened = true;
  await wrapper.vm.$nextTick();

  await wrapper.find(`[data-path="${path}"]`).trigger('click');

  wrapper.findAll('button').forEach((button) => {
    if (button.text() === 'Select') {
      button.trigger('click');
    }
  });

  await new Promise((resolve) => setTimeout(resolve));
}

describe('SectionImagePicker', () => {
  beforeEach(() => {
    files.value = LIBRARY;
    vi.clearAllMocks();
  });

  it('points the section at a file it already owns', async () => {
    const wrapper = mountPicker();

    await openAndPick(wrapper, 'themes/default/sections/7/mine.webp');

    expect(copySectionMedia).not.toHaveBeenCalled();
    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toBe('themes/default/sections/7/mine.webp');
  });

  it('copies a file another section owns before pointing at it', async () => {
    copySectionMedia.mockReturnValue(
      requestOf({ path: 'themes/default/sections/7/copy.webp', url: '/storage/copy.webp' })
    );

    const wrapper = mountPicker();

    await openAndPick(wrapper, 'themes/default/sections/9/theirs.webp');

    expect(copySectionMedia).toHaveBeenCalledWith(7, 'themes/default/sections/9/theirs.webp');
    expect(remember).toHaveBeenCalledWith(
      expect.objectContaining({ path: 'themes/default/sections/7/copy.webp', section: 7 })
    );
    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toBe('themes/default/sections/7/copy.webp');
  });

  it('draws what it is pointed at, read from the library', () => {
    const wrapper = mountPicker('themes/default/sections/7/mine.webp');

    expect(wrapper.find('img').attributes('src')).toBe('/storage/mine.webp');
  });

  it('draws a file stored under the path an installer seeded it as', () => {
    const wrapper = mountPicker('storage/themes/default/sections/7/mine.webp');

    expect(wrapper.find('img').attributes('src')).toBe('/storage/mine.webp');
  });

  it('lets go of the file it was pointed at', async () => {
    const wrapper = mountPicker('themes/default/sections/7/mine.webp');

    await wrapper.find('button[aria-label="Remove"]').trigger('click');

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toBeNull();
  });

  it('offers nothing until the section it belongs to is stored', () => {
    const wrapper = mountPicker(null, fakeEditor('temp-block'));

    expect(wrapper.find('button[disabled]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Available once the section is saved');
  });
});
