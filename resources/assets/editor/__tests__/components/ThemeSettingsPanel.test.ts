import { mount } from '@vue/test-utils';
import { defineComponent, h, nextTick } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import ThemeSettingsPanel from '../../components/ThemeSettingsPanel.vue';
import { createState } from '../../state';

vi.mock('../../api', () => ({
  persistThemeSettings: vi.fn(() => ({
    onSuccess: vi.fn(),
    onError: vi.fn(),
    execute: vi.fn(),
  })),
}));

vi.mock('../../craftile/plugin', () => ({
  CRAFTILE_EDITOR: Symbol('CRAFTILE_EDITOR'),
}));

const PropertyFieldStub = defineComponent({
  props: ['field', 'modelValue'],
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    return () => h('button', {
      type: 'button',
      onClick: () => emit('update:modelValue', 'updated value'),
    }, props.modelValue);
  },
});

describe('ThemeSettingsPanel', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('marks the editor as having unpublished edits when a theme setting changes', async () => {
    const state = createState({
      theme: {
        code: 'test-theme',
        settings: {
          heading: 'Original value',
        },
        settingsSchema: [
          {
            name: 'General',
            settings: [
              {
                id: 'heading',
                type: 'text',
                label: 'Heading',
                default: '',
              },
            ],
          },
        ],
      } as any,
    });

    const wrapper = mount(ThemeSettingsPanel, {
      global: {
        stubs: {
          'Accordion.Root': { template: '<div><slot /></div>' },
          'Accordion.Item': { template: '<div><slot /></div>' },
          'Accordion.ItemTrigger': { template: '<button type="button"><slot /></button>' },
          'Accordion.ItemIndicator': { template: '<span><slot /></span>' },
          'Accordion.ItemContent': { template: '<div><slot /></div>' },
          PropertyField: PropertyFieldStub,
          'i-heroicons-chevron-down': true,
        },
      },
    });

    expect(state.haveEdits).toBe(false);

    await wrapper.getComponent(PropertyFieldStub).trigger('click');
    await nextTick();

    expect(state.theme?.settings.heading).toBe('updated value');
    expect(state.haveEdits).toBe(true);
  });
});
