import { mount } from '@vue/test-utils';
import { defineComponent, h, nextTick, ref } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import TypographyPresetEditor from '../../components/TypographyPresetEditor.vue';

const fonts = [
  {
    slug: 'single-weight',
    name: 'Single Weight',
    weights: [400],
    styles: ['normal'],
  },
  {
    slug: 'multi-weight',
    name: 'Multi Weight',
    weights: [400, 700],
    styles: ['normal', 'italic'],
  },
];

const findFont = vi.fn((slug: string) => fonts.find((font) => font.slug === slug));

vi.mock('../../composables/useBunnyFonts', () => ({
  useBunnyFonts: () => ({
    findFont,
  }),
}));

const FontPickerStub = defineComponent({
  props: {
    modelValue: {
      type: Object,
      default: null,
    },
  },
  emits: ['update:modelValue'],
  setup(_, { emit }) {
    return () => h('button', {
      type: 'button',
      onClick: () => emit('update:modelValue', fonts[0]),
    }, 'Choose font');
  },
});

const PropertyFieldStub = defineComponent({
  props: ['field', 'modelValue'],
  emits: ['update:modelValue'],
  template: '<div />',
});

function createPreset(overrides: Record<string, unknown> = {}) {
  return {
    fontFamily: null,
    fontStyle: 'normal',
    fontWeight: 400,
    fontSize: 'base',
    lineHeight: 'normal',
    letterSpacing: 'normal',
    textTransform: 'none',
    ...overrides,
  };
}

async function mountEditor(modelValue = createPreset()) {
  const Harness = defineComponent({
    components: { TypographyPresetEditor },
    setup() {
      const value = ref(modelValue);
      const updates = ref<any[]>([]);

      function updateValue(nextValue: any) {
        updates.value.push(nextValue);
        value.value = nextValue;
      }

      return { value, updates, updateValue };
    },
    template: '<TypographyPresetEditor :model-value="value" @update:model-value="updateValue" />',
  });

  const wrapper = mount(Harness, {
    global: {
      stubs: {
        FontPicker: FontPickerStub,
        PropertyField: PropertyFieldStub,
      },
    },
  });

  await nextTick();

  return { wrapper, updates: wrapper.vm.updates };
}

describe('TypographyPresetEditor', () => {
  beforeEach(() => {
    findFont.mockClear();
  });

  it('persists only the selected single-weight font slug', async () => {
    const { wrapper, updates } = await mountEditor();

    await wrapper.get('button').trigger('click');
    await nextTick();
    await nextTick();

    expect(updates).toHaveLength(1);
    expect(updates[0].fontFamily).toBe('single-weight');
    expect(updates[0].fontWeight).toBe(400);
    expect(updates[0].fontStyle).toBe('normal');
  });

  it('keeps an existing font slug unchanged when Bunny metadata is available', async () => {
    const { updates } = await mountEditor(createPreset({
      fontFamily: 'multi-weight',
      fontWeight: 400,
      fontStyle: 'normal',
    }));

    await nextTick();

    expect(updates).toHaveLength(0);
  });
});
