import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import FiltersField from '../../components/FiltersField.vue';
import SelectInput from '../../components/SelectInput.vue';

const KEYS = [
  {
    value: 'sort',
    label: 'Sort',
    options: [
      { value: 'name-asc', label: 'Name A-Z' },
      { value: 'name-desc', label: 'Name Z-A' },
    ],
  },
  { value: 'limit', label: 'Limit' },
  {
    value: 'category_id',
    label: 'Category',
    multiple: true,
    options: [
      { value: '1', label: 'Men' },
      { value: '2', label: 'Women' },
    ],
  },
];

function mountField(modelValue: Record<string, any> | null = null, keys: any[] = KEYS) {
  return mount(FiltersField, {
    props: {
      field: { id: 'filters', type: 'filters', label: 'Filters', keys, addLabel: 'Add a filter' },
      modelValue,
    },
  });
}

function lastModel(wrapper: ReturnType<typeof mountField>) {
  const emitted = wrapper.emitted('update:modelValue');

  return emitted?.[emitted.length - 1]?.[0];
}

function addButton(wrapper: ReturnType<typeof mountField>) {
  return wrapper.findAll('button').at(-1)!;
}

function removeButton(wrapper: ReturnType<typeof mountField>, index: number) {
  return wrapper.findAll('button[title="Remove filter"]')[index];
}

/**
 * The pickers of one kind, in row order. A row draws its key first, then the
 * value when the key offers a list to pick from.
 */
function selects(wrapper: ReturnType<typeof mountField>, role: 'key' | 'value') {
  const position = role === 'key' ? 0 : 1;

  return wrapper
    .findAll('[data-filter-row]')
    .map((row) => row.findAllComponents(SelectInput)[position])
    .filter(Boolean);
}

function optionsOf(select: any): string[] {
  return select.props('options').map((option: any) => option.value);
}

describe('FiltersField', () => {
  it('draws a row per stored filter, in the order they are stored', () => {
    const wrapper = mountField({ sort: 'name-desc', limit: '8' });

    expect(selects(wrapper, 'key').map((select) => select.props('modelValue'))).toEqual(['sort', 'limit']);
  });

  it('picks the value from a list when the key declares one, and takes free text otherwise', () => {
    const wrapper = mountField({ sort: 'name-desc', limit: '8' });

    expect(selects(wrapper, 'value')).toHaveLength(1);
    expect(wrapper.findAll('[data-filter="text"]')).toHaveLength(1);
    expect((wrapper.find('[data-filter="text"]').element as HTMLInputElement).value).toBe('8');
  });

  it('writes the rows back as the map the storefront reads', async () => {
    const wrapper = mountField({ sort: 'name-desc' });

    await selects(wrapper, 'value')[0].vm.$emit('update:modelValue', 'name-asc');

    expect(lastModel(wrapper)).toEqual({ sort: 'name-asc' });
  });

  it('starts a new filter on the first key not in use, with its first value', async () => {
    const wrapper = mountField({ sort: 'name-desc' });

    await addButton(wrapper).trigger('click');

    expect(lastModel(wrapper)).toEqual({ sort: 'name-desc', limit: '' });
  });

  it('offers no more filters once every key is in use', () => {
    const wrapper = mountField({ sort: 'name-desc', limit: '8', category_id: '1' });

    expect(addButton(wrapper).attributes('disabled')).toBeDefined();
  });

  it('leaves a key that is taken out of another row\'s choices', () => {
    const wrapper = mountField({ sort: 'name-desc', limit: '8' });

    expect(optionsOf(selects(wrapper, 'key')[0])).toEqual(['sort', 'category_id']);
  });

  it('drops a row the merchant removed', async () => {
    const wrapper = mountField({ sort: 'name-desc', limit: '8' });

    await removeButton(wrapper, 0).trigger('click');

    expect(lastModel(wrapper)).toEqual({ limit: '8' });
  });

  it('changing a key takes the first value the new key offers', async () => {
    const wrapper = mountField({ limit: '8' });

    await selects(wrapper, 'key')[0].vm.$emit('update:modelValue', 'sort');

    expect(lastModel(wrapper)).toEqual({ sort: 'name-asc' });
  });

  it('holds several values as the comma separated list the api reads', async () => {
    const wrapper = mountField({ category_id: '1,2' });
    const value = selects(wrapper, 'value')[0];

    expect(value.props('multiple')).toBe(true);
    expect(value.props('modelValue')).toEqual(['1', '2']);

    await value.vm.$emit('update:modelValue', ['2']);

    expect(lastModel(wrapper)).toEqual({ category_id: '2' });
  });

  it('keeps a stored value the key no longer offers, rather than reading blank', () => {
    const wrapper = mountField({ sort: 'price-asc' });

    expect(optionsOf(selects(wrapper, 'value')[0])).toEqual(['price-asc', 'name-asc', 'name-desc']);
  });

  it('draws nothing but the add button when no filter is stored', () => {
    const wrapper = mountField(null);

    expect(wrapper.findAllComponents(SelectInput)).toHaveLength(0);
    expect(wrapper.findAll('button')).toHaveLength(1);
    expect(removeButton(wrapper, 0)).toBeUndefined();
    expect(wrapper.text()).toContain('Add a filter');
  });
});
