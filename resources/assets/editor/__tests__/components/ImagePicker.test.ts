import { mount } from '@vue/test-utils';
import { describe, expect, it, vi, beforeEach } from 'vitest';
import { nextTick, reactive } from 'vue';

const { CRAFTILE_EDITOR } = vi.hoisted(() => ({
  CRAFTILE_EDITOR: Symbol('CRAFTILE_EDITOR'),
}));

vi.mock('../../craftile/plugin', () => ({ CRAFTILE_EDITOR }));

vi.mock('../../composables/http', () => {
  const request = () => ({
    data: { value: null },
    execute: vi.fn(),
    onSuccess: vi.fn(),
    onError: vi.fn(),
  });

  return { useHttpClient: () => ({ get: request, postFormData: request }) };
});

import ImagePicker from '../../components/ImagePicker.vue';
import { createState } from '../../state';

const ORIGINAL = { path: 'original.webp', alt: '', focalPoint: { x: 50, y: 50 } };

function fakeEditor() {
  const listeners = new Map<string, Set<(payload: any) => void>>();

  const engine = {
    on: vi.fn((event: string, listener: (payload: any) => void) => {
      if (!listeners.has(event)) {
        listeners.set(event, new Set());
      }

      listeners.get(event)!.add(listener);

      return () => listeners.get(event)!.delete(listener);
    }),
    emit(event: string, payload: any = {}) {
      listeners.get(event)?.forEach((listener) => listener(payload));
    },
    listening() {
      return [...listeners.values()].reduce((count, set) => count + set.size, 0);
    },
    undo: vi.fn(() => true),
  };

  return {
    engine,
    ui: { state: reactive({ selectedBlockId: 'block-1' as string | null }), toast: vi.fn() },
  };
}

function mountPicker(editor = fakeEditor(), modelValue: any = ORIGINAL) {
  const wrapper = mount(ImagePicker, {
    props: {
      field: { id: 'image', type: 'image', label: 'Image' } as any,
      modelValue,
      // Craftile turns every write into a property command, one undo step each.
      'onUpdate:modelValue': (value: any) => {
        editor.engine.emit('block:property:set', {
          blockId: editor.ui.state.selectedBlockId,
          key: 'image',
          value,
        });
        wrapper.setProps({ modelValue: value });
      },
    },
    global: {
      provide: { [CRAFTILE_EDITOR]: editor },
      stubs: {
        ImagePreview: { props: ['image', 'selected'], template: '<button :data-path="image.path" />' },
        Button: { template: '<button><slot /></button>' },
        teleport: true,
      },
    },
  });

  return { wrapper, editor };
}

async function open(wrapper: ReturnType<typeof mountPicker>['wrapper']) {
  await wrapper.findAll('button').find((button) => button.text() === 'Change image')!.trigger('click');
  await nextTick();
}

async function pick(wrapper: ReturnType<typeof mountPicker>['wrapper'], path: string) {
  await wrapper.find(`[data-path="${path}"]`).trigger('click');
  await nextTick();
}

async function press(wrapper: ReturnType<typeof mountPicker>['wrapper'], label: string) {
  await wrapper.findAll('button').find((button) => button.text() === label)!.trigger('click');
  await nextTick();
}

function writes(wrapper: ReturnType<typeof mountPicker>['wrapper']) {
  return wrapper.emitted('update:modelValue') ?? [];
}

describe('ImagePicker', () => {
  beforeEach(() => {
    (window as any).editorConfig = { imagesBaseUrl: '/storage', routes: { uploadImage: '/upload', listImages: '/images' } };

    createState({
      images: [
        { path: 'original.webp', url: '/original.webp', name: 'original.webp' },
        { path: 'first.webp', url: '/first.webp', name: 'first.webp' },
        { path: 'second.webp', url: '/second.webp', name: 'second.webp' },
      ] as any,
    });
  });

  it('writes nothing when cancelled without a pick', async () => {
    const { wrapper, editor } = mountPicker();

    await open(wrapper);
    await press(wrapper, 'Cancel');

    expect(writes(wrapper)).toHaveLength(0);
    expect(editor.engine.undo).not.toHaveBeenCalled();
    expect(editor.engine.listening()).toBe(0);
  });

  it('undoes its own picks when cancelled, instead of adding a step', async () => {
    const { wrapper, editor } = mountPicker();

    await open(wrapper);
    await pick(wrapper, 'first.webp');
    await pick(wrapper, 'second.webp');
    await press(wrapper, 'Cancel');

    expect(writes(wrapper)).toHaveLength(2);
    expect(editor.engine.undo).toHaveBeenCalledTimes(2);
    expect(editor.engine.listening()).toBe(0);
  });

  it('writes the original back when something else was edited meanwhile', async () => {
    const { wrapper, editor } = mountPicker();

    await open(wrapper);
    await pick(wrapper, 'first.webp');
    editor.engine.emit('block:remove', { blockId: 'block-2' });
    await press(wrapper, 'Cancel');

    expect(editor.engine.undo).not.toHaveBeenCalled();
    expect(writes(wrapper).at(-1)![0]).toMatchObject({ path: 'original.webp' });
  });

  it('does not undo past an undo made while open', async () => {
    const { wrapper, editor } = mountPicker();

    await open(wrapper);
    await pick(wrapper, 'first.webp');

    // Ctrl+Z reverts the pick, emitting a property change for this field.
    editor.engine.emit('block:property:set', { blockId: 'block-1', key: 'image', value: ORIGINAL });
    editor.engine.emit('undo');
    await wrapper.setProps({ modelValue: ORIGINAL });

    await press(wrapper, 'Cancel');

    expect(editor.engine.undo).not.toHaveBeenCalled();
    expect(writes(wrapper)).toHaveLength(1);
  });

  it('keeps the pick on select', async () => {
    const { wrapper, editor } = mountPicker();

    await open(wrapper);
    await pick(wrapper, 'first.webp');
    await press(wrapper, 'Select');

    expect(editor.engine.undo).not.toHaveBeenCalled();
    expect(writes(wrapper)).toHaveLength(1);
    expect(editor.engine.listening()).toBe(0);
  });

  it('keeps the pick and closes when another block is selected', async () => {
    const { wrapper, editor } = mountPicker();

    await open(wrapper);
    await pick(wrapper, 'first.webp');

    editor.ui.state.selectedBlockId = 'block-2';
    await nextTick();

    expect((wrapper.vm as any).opened).toBe(false);
    expect(editor.engine.undo).not.toHaveBeenCalled();
    expect(writes(wrapper)).toHaveLength(1);
    expect(editor.engine.listening()).toBe(0);
  });

  it('stops listening when removed while open', async () => {
    const { wrapper, editor } = mountPicker();

    await open(wrapper);
    expect(editor.engine.listening()).toBeGreaterThan(0);

    wrapper.unmount();

    expect(editor.engine.listening()).toBe(0);
  });

  it('tells a disk path from a url quickly, whatever the path looks like', () => {
    // A domain pattern that could match one string in many ways took seconds on this.
    const path = '0'.repeat(27) + '!';
    const started = performance.now();

    const { wrapper } = mountPicker(fakeEditor(), { path, alt: '', focalPoint: { x: 50, y: 50 } });

    expect(performance.now() - started).toBeLessThan(1000);
    expect(wrapper.find('img').attributes('src')).toBe(`/storage/${path}`);
  });

  it('keeps a url as it is', () => {
    const { wrapper } = mountPicker(fakeEditor(), { path: 'https://cdn.example.com/a.webp', alt: '', focalPoint: { x: 50, y: 50 } });

    expect(wrapper.find('img').attributes('src')).toBe('https://cdn.example.com/a.webp');
  });
});
