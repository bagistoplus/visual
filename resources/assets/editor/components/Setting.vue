<script lang="ts">
  import TextSetting from './TextInput.vue';
  import TextareaSetting from './Textarea.vue';
  import CheckboxSetting from './Checkbox.vue';
  import NumberSetting from './NumberInput.vue';
  import RadioSetting from './RadioGroup.vue';
  import RangeSetting from './RangeInput.vue';
  import SelectSetting from './Select.vue';
  import ColorSetting from './ColorPicker.vue';
  import ImageSetting from './ImagePicker.vue';
  import CategorySetting from './CategoryPicker.vue';
  import ProductSetting from './ProductPicker.vue';
  import CmsPageSetting from './CmsPagePicker.vue';
  import LinkSetting from './LinkPicker.vue';
  import RichtextSetting from './RichtextEditor.vue';
  import FontSetting from './FontPicker.vue';
  import IconSetting from './IconPicker.vue';
  import ColorSchemeGroupSetting from './ColorSchemeGroup.vue';
  import ColorSchemeSetting from './ColorSchemePicker.vue';

  const InlineRichtextSetting = RichtextSetting;

  export default {
    components: {
      TextSetting,
      TextareaSetting,
      CheckboxSetting,
      NumberSetting,
      RadioSetting,
      RangeSetting,
      SelectSetting,
      ColorSetting,
      ImageSetting,
      CategorySetting,
      ProductSetting,
      CmsPageSetting,
      LinkSetting,
      RichtextSetting,
      InlineRichtextSetting,
      FontSetting,
      IconSetting,
      ColorSchemeGroupSetting,
      ColorSchemeSetting
    },
  };
</script>

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
  const inline = computed(() => ['checkbox'].includes(props.setting.type))
</script>

<template>
  <div class="mb-4 last:mb-0">
    <div :class="{ 'flex gap-2 flex-row-reverse justify-end items-center': inline }">
      <label
        v-if="setting.label"
        class="text-sm font-medium block"
        :class="{ 'mb-1': !inline }"
      >{{ setting.label }}</label>

      <component
        :is="setting.component"
        v-bind="{ ...setting, label: null }"
        :model-value="props.value"
        @update:modelValue="(val: any) => emit('input', val)"
      />
    </div>

    <small
      v-if="setting.info"
      class="text-xs italic leading-[0.5rem]"
    >{{ setting.info }}</small>
  </div>
</template>
