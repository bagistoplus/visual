<script setup lang="ts">
  import { useStore } from '../store';
  import { Setting } from '../types';

  const props = defineProps<{
    setting: Setting;
    value: any;
  }>();

  const emit = defineEmits<{
    (e: 'input', value: boolean | number | string): void;
  }>();

  const store = useStore();
</script>

<template>
  <div class="mb-6 last:mb-0">
    <TextInput
      v-if="setting.type === 'text'"
      :id="setting.id"
      :label="setting.label"
      :modelValue="props.value"
      @update:modelValue="(val: string) => emit('input', val)"
    />

    <Textarea
      v-if="setting.type === 'textarea'"
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
      v-else-if="setting.type === 'select'"
      :label="setting.label"
      :options="setting.options"
      :model-value="value"
      @update:model-value="(v: string) => emit('input', v)"
    />

    <ColorPicker
      v-else-if="setting.type === 'color'"
      :label="setting.label"
      :used-colors="store.usedColors"
      :model-value="value"
      @update:model-value="(v: string) => emit('input', v)"
    />

    <ImagePicker
      v-else-if="setting.type === 'image'"
      :label="setting.label"
      :model-value="value"
      @update:model-value="(v: string) => emit('input', v)"
    />

    <CategoryPicker
      v-else-if="setting.type === 'category'"
      :label="setting.label"
      :model-value="value"
      @update:model-value="(v: string) => emit('input', v)"
    />

    <ProductPicker
      v-else-if="setting.type === 'product'"
      :label="setting.label"
      :model-value="value"
      @update:model-value="(v: string) => emit('input', v)"
    />

    <CmsPagePicker
      v-else-if="setting.type === 'cms_page'"
      :label="setting.label"
      :model-value="value"
      @update:model-value="(v: string) => emit('input', v)"
    />

    <LinkPicker
      v-else-if="setting.type === 'link'"
      :label="setting.label"
      :model-value="value"
      @update:model-value="(v: string) => emit('input', v)"
    />

    <small v-if="setting.info">{{ setting.info }}</small>
  </div>
</template>
