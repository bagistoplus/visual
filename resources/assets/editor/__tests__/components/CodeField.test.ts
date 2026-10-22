import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';

const { CRAFTILE_EDITOR } = vi.hoisted(() => ({
  CRAFTILE_EDITOR: Symbol('CRAFTILE_EDITOR'),
}));

vi.mock('../../craftile/plugin', () => ({ CRAFTILE_EDITOR }));

const { uploadSectionMedia } = vi.hoisted(() => ({ uploadSectionMedia: vi.fn() }));

vi.mock('../../api', () => ({ uploadSectionMedia }));

import CodeField from '../../components/CodeField.vue';

function fakeEditor(selectedBlockId: string | null = 'section-7') {
  return {
    ui: {
      state: { selectedBlockId },
      toast: vi.fn(),
    },
  };
}

function mountField(
  field: Record<string, unknown> = {},
  modelValue: string | null = null,
  editor: any = fakeEditor()
) {
  return mount(CodeField, {
    props: {
      field: { id: 'html', type: 'code', label: 'Markup', ...field },
      modelValue,
    },
    global: {
      provide: { [CRAFTILE_EDITOR]: editor },
      stubs: { 'i-lucide-image': { template: '<span />' } },
    },
  });
}

function uploadOf(media: { url: string; type: string }) {
  return () => {
    const success: Array<(value: any) => void> = [];

    return {
      onSuccess: (callback: (value: any) => void) => success.push(callback),
      onError: () => {},
      execute: async () => success.forEach((callback) => callback(media)),
    };
  };
}

async function addFile(wrapper: ReturnType<typeof mountField>) {
  const input = wrapper.find('input[type="file"]');

  Object.defineProperty(input.element, 'files', { value: [new File(['x'], 'shot.png')] });

  await input.trigger('change');
  await new Promise((resolve) => setTimeout(resolve));
  await wrapper.vm.$nextTick();
}

describe('CodeField', () => {
  it('names the language the field is written in, defaulting to html', () => {
    expect(mountField({ language: 'css' }).text()).toContain('css');
    expect(mountField().text()).toContain('html');
  });

  it('shows what is stored, and an empty box when nothing is', () => {
    expect((mountField({}, '<p>Hi</p>').find('textarea').element as HTMLTextAreaElement).value).toBe('<p>Hi</p>');
    expect((mountField().find('textarea').element as HTMLTextAreaElement).value).toBe('');
  });

  it('reports what was typed', async () => {
    const wrapper = mountField({}, '<p>Hi</p>');

    await wrapper.find('textarea').setValue('<p>There</p>');

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toBe('<p>There</p>');
  });

  it('holds what is being typed while the server rewrites what is stored', async () => {
    const wrapper = mountField({}, '<p>Hi');
    const area = wrapper.find('textarea');

    await area.trigger('focus');
    await area.setValue('<p>Hi there');

    // What a save hands back, purified, while the merchant is still typing.
    await wrapper.setProps({ modelValue: '<p>Hi there</p>' });

    expect((area.element as HTMLTextAreaElement).value).toBe('<p>Hi there');
  });

  it('takes what is stored once the merchant leaves the box', async () => {
    const wrapper = mountField({}, '<p>Hi');
    const area = wrapper.find('textarea');

    await area.trigger('focus');
    await area.setValue('<p>Hi there');
    await wrapper.setProps({ modelValue: '<p>Hi there</p>' });
    await area.trigger('blur');

    expect((area.element as HTMLTextAreaElement).value).toBe('<p>Hi there</p>');
  });

  it('shows what is stored when a change lands while the box is not being typed in', async () => {
    const wrapper = mountField({}, '<p>Hi</p>');

    await wrapper.setProps({ modelValue: '<p>Elsewhere</p>' });

    expect((wrapper.find('textarea').element as HTMLTextAreaElement).value).toBe('<p>Elsewhere</p>');
  });

  it('colours the markup behind the box it is typed in', () => {
    const wrapper = mountField({ language: 'html' }, '<p class="lead">Hi</p>');
    const painted = wrapper.find('pre').html();

    expect(painted).toContain('token tag');
    expect(painted).toContain('token attr-name');
    expect(wrapper.find('pre').text()).toBe('<p class="lead">Hi</p>');
  });

  it('colours css with its own grammar', () => {
    const painted = mountField({ language: 'css' }, '.lead { color: red; }').find('pre').html();

    expect(painted).toContain('token selector');
    expect(painted).toContain('token property');
  });

  it('escapes what is typed rather than rendering it', () => {
    const painted = mountField({}, '<script>alert(1)</script>').find('pre');

    expect(painted.element.querySelector('script')).toBeNull();
    expect(painted.text()).toBe('<script>alert(1)</script>');
  });

  it('keeps the coloured layer scrolled with the box', async () => {
    const wrapper = mountField({}, 'a\nb\nc');
    const area = wrapper.find('textarea');

    Object.defineProperty(area.element, 'scrollTop', { value: 40, writable: true });
    await area.trigger('scroll');

    expect((wrapper.find('pre').element as HTMLPreElement).scrollTop).toBe(40);
  });

  it('offers media only where there is somewhere to put a tag', () => {
    expect(mountField({ language: 'html' }).find('input[type="file"]').exists()).toBe(true);
    expect(mountField({ language: 'css' }).find('input[type="file"]').exists()).toBe(false);
  });

  it('offers no media for a section that is not stored yet', () => {
    const wrapper = mountField({}, null, fakeEditor('a-block-the-editor-named'));

    expect(wrapper.find('input[type="file"]').exists()).toBe(false);
  });

  it('writes an uploaded image where the cursor is', async () => {
    uploadSectionMedia.mockImplementation(uploadOf({ url: '/storage/shot.png', type: 'image' }));

    const wrapper = mountField({}, '<p>a</p>');
    const area = wrapper.find('textarea');

    (area.element as HTMLTextAreaElement).setSelectionRange(3, 3);
    await addFile(wrapper);

    expect(uploadSectionMedia).toHaveBeenCalledWith(7, expect.any(File));
    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toBe(
      '<p><img src="/storage/shot.png" alt="" class="w-full" />a</p>'
    );
  });

  it('writes an uploaded clip as a video tag', async () => {
    uploadSectionMedia.mockImplementation(uploadOf({ url: '/storage/clip.mp4', type: 'video' }));

    const wrapper = mountField({}, '');

    await addFile(wrapper);

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toBe(
      '<video src="/storage/clip.mp4" controls playsinline class="w-full"></video>'
    );
  });

  it('indents on tab rather than leaving the field', async () => {
    const wrapper = mountField({}, 'ab');
    const area = wrapper.find('textarea');

    (area.element as HTMLTextAreaElement).setSelectionRange(1, 1);
    await area.trigger('keydown.tab');

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toBe('a  b');
  });
});
