import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import OptionListbox from '../../components/OptionListbox.vue';
import SelectInput from '../../components/SelectInput.vue';

const OPTIONS = [
  { value: 'name-asc', label: 'Name A-Z' },
  { value: 'name-desc', label: 'Name Z-A' },
];

function mountSelect(props: Record<string, unknown> = {}) {
  return mount(SelectInput, { props: { options: OPTIONS, ...props } });
}

function trigger(wrapper: ReturnType<typeof mountSelect>) {
  return wrapper.find('[data-part="trigger"]');
}

/** The popover only opens on a real pointer sequence, which jsdom has none of. */
async function open(wrapper: ReturnType<typeof mountSelect>) {
  (wrapper.vm as any).opened = true;

  await wrapper.vm.$nextTick();
}

describe('SelectInput', () => {
  it('reads as the label of what is held', () => {
    expect(trigger(mountSelect({ modelValue: 'name-desc' })).text()).toBe('Name Z-A');
  });

  it('reads as every label when it holds several', () => {
    const wrapper = mountSelect({ multiple: true, modelValue: ['name-asc', 'name-desc'] });

    expect(trigger(wrapper).text()).toBe('Name A-Z, Name Z-A');
  });

  it('reads as the placeholder while it holds nothing', () => {
    expect(trigger(mountSelect({ modelValue: '', placeholder: 'Pick one' })).text()).toBe('Pick one');
  });

  it('puts what is passed from outside on the box, the popover root drawing none', () => {
    const wrapper = mount(SelectInput, {
      props: { options: OPTIONS, modelValue: 'name-asc' },
      attrs: { class: 'flex-1', 'data-role': 'key' },
    });

    expect(trigger(wrapper).classes()).toContain('flex-1');
    expect(trigger(wrapper).attributes('data-role')).toBe('key');
  });

  it('falls back to a value the options no longer name', () => {
    expect(trigger(mountSelect({ modelValue: 'gone' })).text()).toBe('gone');
  });

  it('hands the list what it holds and reports back what was picked', async () => {
    const wrapper = mountSelect({ modelValue: 'name-asc' });

    await open(wrapper);

    const listbox = wrapper.findComponent(OptionListbox);

    expect(listbox.props('modelValue')).toBe('name-asc');

    await listbox.vm.$emit('update:modelValue', 'name-desc');

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toBe('name-desc');
  });

  it('closes on a pick', async () => {
    const wrapper = mountSelect({ modelValue: 'name-asc' });

    await open(wrapper);
    await wrapper.findComponent(OptionListbox).vm.$emit('select', 'name-desc');
    await wrapper.vm.$nextTick();

    expect((wrapper.vm as any).opened).toBe(false);
  });

  it('stays open when it holds several, so the rest can be picked', async () => {
    const wrapper = mountSelect({ multiple: true, modelValue: ['name-asc'] });

    await open(wrapper);
    await wrapper.findComponent(OptionListbox).vm.$emit('select', 'name-desc');
    await wrapper.vm.$nextTick();

    expect((wrapper.vm as any).opened).toBe(true);
  });
});

describe('SelectInput width', () => {
  function positioner(wrapper: ReturnType<typeof mountSelect>) {
    return wrapper.find('[data-part="positioner"]').classes();
  }

  const LONG = Array.from({ length: 9 }, (_, index) => ({
    value: String(index),
    label: `Category ${index}`,
  }));

  it('takes the width it drops from for a list short enough to take in', async () => {
    const wrapper = mountSelect();

    (wrapper.vm as any).opened = true;
    await wrapper.vm.$nextTick();

    expect(positioner(wrapper)).toContain('w-[var(--reference-width)]');
  });

  it('takes more room for a long list, such as the categories', async () => {
    const wrapper = mountSelect({ options: LONG });

    (wrapper.vm as any).opened = true;
    await wrapper.vm.$nextTick();

    expect(positioner(wrapper)).toContain('w-72');
  });

  it('takes more room for a picker holding several', async () => {
    const wrapper = mountSelect({ multiple: true, modelValue: [] });

    (wrapper.vm as any).opened = true;
    await wrapper.vm.$nextTick();

    expect(positioner(wrapper)).toContain('w-72');
  });
});
