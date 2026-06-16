import { mount } from '@vue/test-utils';
import { defineComponent, h, nextTick } from 'vue';
import { afterEach, describe, expect, it, vi } from 'vitest';
import TypographyPresets from '../../components/TypographyPresets.vue';

vi.mock('@ark-ui/vue/dialog', async () => {
  const { defineComponent, h } = await import('vue');

  const Slot = defineComponent({
    setup(_, { slots }) {
      return () => h('div', slots.default?.());
    },
  });

  return {
    Dialog: {
      Root: defineComponent({
        props: {
          open: {
            type: Boolean,
            default: false,
          },
        },
        emits: ['update:open'],
        setup(props, { slots }) {
          return () => props.open ? h('div', slots.default?.()) : null;
        },
      }),
      Positioner: Slot,
      Content: Slot,
      CloseTrigger: Slot,
    },
  };
});

const TypographyPresetPreviewStub = defineComponent({
  props: ['preset', 'label'],
  template: '<div>{{ preset.name || label }}</div>',
});

const TypographyPresetEditorStub = defineComponent({
  template: '<div />',
});

function createPreset() {
  return {
    name: 'cupcake',
    fontFamily: null,
    fontStyle: 'normal',
    fontWeight: 400,
    fontSize: 'base',
    lineHeight: 'normal',
    letterSpacing: 'normal',
    textTransform: 'none',
  };
}

describe('TypographyPresets', () => {
  afterEach(() => {
    window.editorConfig.messages = {};
  });

  it('localizes the edit header as a single phrase with the preset name', async () => {
    window.editorConfig.messages = {
      'Editing :name': 'Édition de :name',
    };

    const wrapper = mount(TypographyPresets, {
      props: {
        field: {
          label: 'Typography presets',
          presets: {},
        },
        modelValue: {
          cupcake: createPreset(),
        },
      },
      global: {
        stubs: {
          TypographyPresetPreview: TypographyPresetPreviewStub,
          TypographyPresetEditor: TypographyPresetEditorStub,
          'i-heroicons-pencil': true,
          'i-heroicons-plus': true,
          'i-heroicons-trash': true,
          'i-heroicons-x-mark': true,
        },
      },
    });

    await wrapper.get('button').trigger('click');
    await nextTick();

    expect(wrapper.text()).toContain('Édition de cupcake');
    expect(wrapper.text()).not.toContain('Editing cupcake');
  });
});
