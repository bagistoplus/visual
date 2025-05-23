<script setup lang="ts">
  import { Field } from '@ark-ui/vue/field';
  import { Slider } from '@ark-ui/vue/slider';

  interface Props {
    label?: string;
    min?: number;
    max?: number;
    unit?: string;
    step?: number;
  };

  const props = withDefaults(defineProps<Props>(), {
    step: 1,
    min: 0,
    max: 100
  });

  const value = defineModel<number>({ default: 0 });

  function onInput(input: string) {
    const num = Number(input);
    if (num < props.min) {
      value.value = props.min;
    } else if (num > props.max) {
      value.value = props.max;
    } else {
      value.value = num;
    }
  }

  function onValueChange(details: { value: number[] }) {
    value.value = details.value[0];
  }
</script>

<template>
  <Slider.Root
    :min="min"
    :max="max"
    :step="step"
    :modelValue="[value]"
    class="gap-3 flex flex-col"
    @value-change="onValueChange"
  >
    <Slider.Label
      v-if="label"
      class="text-sm font-medium"
    >
      {{ label }}
    </Slider.Label>

    <div class="flex items-center gap-4">
      <Slider.Control class="flex-1 flex items-center h-2 select-none touch-none">
        <Slider.Track class="flex-1 overflow-hidden h-1 rounded-full bg-gray-100">
          <Slider.Range class="h-2 bg-gray-700" />
        </Slider.Track>
        <Slider.Thumb
          :index="0"
          :key="0"
          class="h-4 w-4 bg-white rounded-full shadow outline-none border-2 border-gray-700"
        />
      </Slider.Control>
      <Field.Root class="flex flex-none items-center w-16 px-2 h-8 rounded border border-gray-300 focus-within:outline-none focus-within:ring focus-within:ring-gray-700">
        <Field.Input
          class="w-full text-sm  outline-none appearance-none"
          type="number"
          :model-value="value"
          :min="min"
          :max="max"
          @update:model-value="onInput"
        />
        <div
          v-if="unit"
          class="text-sm text-gray-500"
        >{{ unit }}</div>
      </Field.Root>
    </div>
  </Slider.Root>
</template>