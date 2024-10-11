<script setup lang="ts">
import { Setting } from '../types';

const props = defineProps<{
  setting: Setting;
  value: any;
}>();

const emit = defineEmits<{
  (e: 'input', value: boolean|number|string): void;
}>();
</script>

<template>
  <div class="mb-6 last:mb-0">
    <TextInput
      v-if="setting.type==='text'"
      :id="setting.id"
      :label="setting.label"
      :modelValue="props.value"
      @update:modelValue="(val: string) => emit('input', val)"
    />

    <Textarea
      v-if="setting.type==='textarea'"
      :id="setting.id"
      :label="setting.label"
      :modelValue="props.value"
      @update:modelValue="(val: string) => emit('input', val)"
    />

    <Checkbox
      v-else-if="setting.type === 'checkbox'"
      :label="setting.label"
      :modelValue="props.value"
      @update:modelValue="(val: any) => emit('input', val)"
    />

    <NumberInput
      v-else-if="setting.type === 'number'"
      :label="setting.label"
      :modelValue="props.value"
      @update:modelValue="(val: any) => emit('input', Number(val))"
    />

    <RadioGroup
      v-else-if="setting.type === 'radio'"
      :label="setting.label"
      :options="setting.options"
      :modelValue="props.value"
      @update:modelValue="(val: any) => emit('input', val)"
    />

    <RangeInput
      v-else-if="setting.type === 'range'"
      :label="setting.label"
      :modelValue="props.value"
      :min="setting.min"
      :max="setting.max"
      :step="setting.step"
      :unit="setting.unit"
      @update:modelValue="(val: number) => emit('input', val)"
    />

    <Select
      v-if="setting.type === 'select'"
      :label="setting.label"
      :options="setting.options"
      :model-value="value"
      @update:model-value="(v: string) => emit('input', v)"
    />

    <small v-if="setting.info">{{ setting.info }}</small>
  </div>
</template>
