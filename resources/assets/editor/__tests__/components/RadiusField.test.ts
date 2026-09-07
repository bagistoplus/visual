import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import RadiusField from '../../components/RadiusField.vue';

const ALL_KEYS = ['none', 'xs', 'sm', 'md', 'lg', 'xl', 'full'];

function mountField(field: Record<string, unknown>, modelValue: string | null = null) {
  return mount(RadiusField, {
    props: {
      field: { id: 'radius', type: 'radius', label: 'Radius', ...field },
      modelValue,
    },
  });
}

describe('RadiusField', () => {
  it('renders one swatch per key of the scale', () => {
    const wrapper = mountField({});
    const buttons = wrapper.findAll('button');

    expect(buttons.map((b) => b.attributes('title'))).toEqual(ALL_KEYS);
    expect(wrapper.text()).toContain('Radius');
  });

  it('restricts swatches to the known keys listed in options', () => {
    const wrapper = mountField({ options: ['none', 'huge', 'full'] });

    expect(wrapper.findAll('button').map((b) => b.attributes('title'))).toEqual(['none', 'full']);
  });

  it('marks the model value as pressed', () => {
    const wrapper = mountField({}, 'lg');

    const pressed = wrapper.findAll('button').filter((b) => b.attributes('aria-pressed') === 'true');

    expect(pressed).toHaveLength(1);
    expect(pressed[0].attributes('title')).toBe('lg');
  });

  it('falls back to the field default, then md, when the model is empty', () => {
    const withDefault = mountField({ default: 'full' });
    const withoutDefault = mountField({});

    expect(withDefault.find('button[aria-pressed="true"]').attributes('title')).toBe('full');
    expect(withoutDefault.find('button[aria-pressed="true"]').attributes('title')).toBe('md');
  });

  it('emits the clicked key', async () => {
    const wrapper = mountField({}, 'md');

    await wrapper.find('button[title="xl"]').trigger('click');

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['xl']);
  });
});
