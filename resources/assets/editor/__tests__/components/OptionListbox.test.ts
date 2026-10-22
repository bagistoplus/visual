import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import OptionListbox from '../../components/OptionListbox.vue';

const OPTIONS = [
  { value: 'name-asc', label: 'Name A-Z' },
  { value: 'name-desc', label: 'Name Z-A' },
  { value: 'price-asc', label: 'Price low to high' },
];

function mountListbox(props: Record<string, unknown> = {}) {
  return mount(OptionListbox, { props: { options: OPTIONS, ...props } });
}

function rows(wrapper: ReturnType<typeof mountListbox>) {
  return wrapper.findAll('a').map((row) => row.text());
}

describe('OptionListbox', () => {
  it('lists every option', () => {
    expect(rows(mountListbox())).toEqual(['Name A-Z', 'Name Z-A', 'Price low to high']);
  });

  it('narrows the list to what was typed, whatever the case', async () => {
    const wrapper = mountListbox();

    await wrapper.find('input').setValue('PRICE');

    expect(rows(wrapper)).toEqual(['Price low to high']);
  });

  it('says so when nothing matches', async () => {
    const wrapper = mountListbox();

    await wrapper.find('input').setValue('nothing');

    expect(rows(wrapper)).toEqual([]);
    expect(wrapper.text()).toContain('No match');
  });

  it('offers no search for a list short enough to read', () => {
    expect(mountListbox({ options: OPTIONS.slice(0, 2) }).find('input').exists()).toBe(false);
    expect(mountListbox().find('input').exists()).toBe(true);
  });

  it('reports the one that was picked', async () => {
    const wrapper = mountListbox({ modelValue: 'name-asc' });

    await wrapper.findAll('a')[1].trigger('click');

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toBe('name-desc');
    expect(wrapper.emitted('select')?.at(-1)?.[0]).toBe('name-desc');
  });

  it('adds to what is held when it holds several', async () => {
    const wrapper = mountListbox({ multiple: true, modelValue: ['name-asc'] });

    await wrapper.findAll('a')[1].trigger('click');

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toEqual(['name-asc', 'name-desc']);
  });

  it('takes back one that is already held', async () => {
    const wrapper = mountListbox({ multiple: true, modelValue: ['name-asc', 'name-desc'] });

    await wrapper.findAll('a')[0].trigger('click');

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toEqual(['name-desc']);
  });

  it('ticks what is held, whether it holds one or several', () => {
    const many = mountListbox({ multiple: true, modelValue: ['name-desc'] });

    expect(many.findAll('a')[1].find('i-heroicons-check').exists()).toBe(true);
    expect(many.findAll('a')[0].find('i-heroicons-check').exists()).toBe(false);

    const one = mountListbox({ modelValue: 'name-asc' });

    expect(one.findAll('a')[0].find('i-heroicons-check').exists()).toBe(true);
    expect(one.findAll('a')[1].find('i-heroicons-check').exists()).toBe(false);
  });

  it('marks what is held with the tick alone, not a colour', () => {
    const wrapper = mountListbox({ modelValue: 'name-asc' });

    expect(wrapper.findAll('a')[0].classes().join(' ')).not.toContain('bg-neutral-1');
    expect(wrapper.findAll('a')[0].classes()).toEqual(wrapper.findAll('a')[1].classes());
  });
});
