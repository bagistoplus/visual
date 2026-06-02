import { mount } from '@vue/test-utils';
import { defineComponent, h, nextTick, ref } from 'vue';
import { describe, expect, it, vi } from 'vitest';
import GradientPicker from '../../components/GradientPicker.vue';

vi.mock('@ark-ui/vue/popover', async () => {
  const { defineComponent, h } = await import('vue');

  const Slot = defineComponent({
    setup(_, { slots }) {
      return () => h('div', slots.default?.());
    },
  });

  return {
    Popover: {
      Root: Slot,
      Trigger: Slot,
      Positioner: Slot,
      Content: Slot,
    },
  };
});

vi.mock('@ark-ui/vue/slider', async () => {
  const { defineComponent, h } = await import('vue');

  const Slot = defineComponent({
    setup(_, { slots }) {
      return () => h('div', slots.default?.());
    },
  });

  return {
    Slider: {
      Root: Slot,
      Label: Slot,
      Control: Slot,
      Track: Slot,
      Range: Slot,
      Thumb: Slot,
    },
  };
});

vi.mock('@craftile/editor/ui', async () => {
  const { defineComponent, h } = await import('vue');

  return {
    Button: defineComponent({
      emits: ['click'],
      setup(_, { emit, slots }) {
        return () => h('button', {
          type: 'button',
          onClick: () => emit('click'),
        }, slots.default?.());
      },
    }),
  };
});

const ColorPickerStub = defineComponent({
  props: ['modelValue', 'label'],
  emits: ['update:modelValue'],
  template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)">',
});

function mountGradientPicker(initialValue: string | null = 'linear-gradient(90deg, #000000 0%, #ffffff 100%)') {
  const Harness = defineComponent({
    components: { GradientPicker },
    setup() {
      const value = ref<string | null>(initialValue);

      return { value };
    },
    template: '<GradientPicker v-model="value" :field="{ label: \'Gradient\' }" />',
  });

  return mount(Harness, {
    global: {
      stubs: {
        ColorPicker: ColorPickerStub,
      },
    },
  });
}

describe('GradientPicker', () => {
  it('keeps the visual editor visible without committing a default gradient when the model value is null', async () => {
    const wrapper = mountGradientPicker(null);
    await nextTick();

    expect(wrapper.vm.value).toBeNull();
    expect(wrapper.find('.cursor-crosshair').exists()).toBe(true);
    expect(wrapper.find('.cursor-move').exists()).toBe(true);
    expect(wrapper.text()).toContain('Edit Color Stop');
    expect(wrapper.find('.h-12').attributes('style')).toContain('background: none');
    expect((wrapper.findAll('input').at(-1)!.element as HTMLInputElement).value).toBe('');
  });

  it('emits a css string when the visual editor changes from a null value', async () => {
    const wrapper = mountGradientPicker(null);
    await nextTick();

    await wrapper.findAll('button')[1].trigger('click');
    await nextTick();

    expect(wrapper.vm.value).toBe('radial-gradient(circle, #000000ff 0%, #ffffffff 100%)');
  });

  it('clears the model value when the css input is emptied', async () => {
    const wrapper = mountGradientPicker();
    await nextTick();

    const input = wrapper.findAll('input').at(-1)!;

    await input.setValue('');
    await nextTick();

    expect(wrapper.vm.value).toBeNull();
    expect(wrapper.find('.cursor-crosshair').exists()).toBe(true);
    expect(wrapper.text()).not.toContain('Enter a single linear-gradient');
  });

  it('keeps the selected stop editor visible when clicking another stop', async () => {
    const wrapper = mountGradientPicker();
    await nextTick();

    expect(wrapper.text()).toContain('Position: 0%');

    const stops = wrapper.findAll('.cursor-move');
    expect(stops).toHaveLength(2);

    await stops[1].trigger('mousedown', { clientX: 100 });
    await nextTick();

    expect(wrapper.text()).toContain('Edit Color Stop');
    expect(wrapper.text()).toContain('Position: 100%');
  });

  it('shows the stop editor for a newly added stop', async () => {
    const wrapper = mountGradientPicker();
    await nextTick();

    const gradientBar = wrapper.find('.cursor-crosshair');
    Object.defineProperty(gradientBar.element, 'getBoundingClientRect', {
      value: () => ({
        left: 0,
        width: 200,
        top: 0,
        height: 16,
        right: 200,
        bottom: 16,
        x: 0,
        y: 0,
        toJSON: () => {},
      }),
    });

    await gradientBar.trigger('click', { clientX: 100 });
    await nextTick();

    expect(wrapper.text()).toContain('Edit Color Stop');
    expect(wrapper.text()).toContain('Position: 50%');
  });

  it('emits canonical css strings after visual edits', async () => {
    const wrapper = mountGradientPicker();
    await nextTick();

    await wrapper.findAll('button')[1].trigger('click');
    await nextTick();

    expect(wrapper.vm.value).toBe('radial-gradient(circle, #000000ff 0%, #ffffffff 100%)');
  });

  it('keeps invalid css as draft input without emitting it', async () => {
    const wrapper = mountGradientPicker();
    await nextTick();

    const previousValue = wrapper.vm.value;
    const input = wrapper.findAll('input').at(-1)!;

    await input.setValue('conic-gradient(red, blue)');
    await nextTick();

    expect(wrapper.vm.value).toBe(previousValue);
    expect(wrapper.text()).toContain('Enter a single linear-gradient');
  });
});
