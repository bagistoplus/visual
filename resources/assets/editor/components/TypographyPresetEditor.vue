<script setup lang="ts">
import { PropertyField } from '@craftile/editor/ui';
import useI18n from '../composables/i18n';
import FontPicker from './FontPicker.vue';
import {
  getFontSizeOptions,
  getLineHeightOptions,
  getLetterSpacingOptions,
  getFontStyleOptions,
  getTextTransformOptions,
} from '../constants/typography';

interface Font {
  slug: string;
  name: string;
  weights: string[];
  styles: string[];
}

interface TypographyPresetValue {
  fontFamily: string | null;
  fontStyle: 'normal' | 'italic';
  fontSize: string | Record<string, string>;
  lineHeight: string | Record<string, string>;
  letterSpacing: string;
  textTransform: 'none' | 'capitalize' | 'uppercase' | 'lowercase';
}

interface Props {
  canDelete?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  canDelete: false,
});

const emit = defineEmits<{
  delete: []
}>();

function handleDelete() {
  emit('delete');
}

const { t } = useI18n();

const fontSizeOptions = getFontSizeOptions(t);
const lineHeightOptions = getLineHeightOptions(t);
const letterSpacingOptions = getLetterSpacingOptions(t);
const fontStyleOptions = getFontStyleOptions(t);
const textTransformOptions = getTextTransformOptions(t);

const model = defineModel<TypographyPresetValue>({
  default: () => ({
    fontFamily: null,
    fontStyle: 'normal',
    fontSize: 'base',
    lineHeight: 'normal',
    letterSpacing: 'normal',
    textTransform: 'none',
  }),
});

const fontSizeField = {
  id: 'fontSize',
  label: t('Font Size'),
  type: 'select',
  options: fontSizeOptions,
  responsive: true,
};

const lineHeightField = {
  id: 'lineHeight',
  label: t('Line Height'),
  type: 'select',
  options: lineHeightOptions,
  responsive: true,
};

const fontStyleField = {
  id: 'fontStyle',
  label: t('Font Style'),
  type: 'select',
  options: fontStyleOptions,
};

const letterSpacingField = {
  id: 'letterSpacing',
  label: t('Letter Spacing'),
  type: 'select',
  options: letterSpacingOptions,
};

const textTransformField = {
  id: 'textTransform',
  label: t('Text Transform'),
  type: 'select',
  options: textTransformOptions,
};

const fontPickerModel = computed({
  get: () => {
    if (!model.value.fontFamily) {
      return null;
    }

    return {
      slug: model.value.fontFamily.toLowerCase().replace(/\s+/g, '-'),
      name: model.value.fontFamily,
      weights: [],
      styles: [],
    };
  },
  set: (value: Font | null) => {
    model.value = { ...model.value, fontFamily: value?.name || null };
  },
});

const fontStyleModel = computed({
  get: () => model.value.fontStyle,
  set: (value) => {
    model.value = { ...model.value, fontStyle: value };
  },
});

const fontSizeModel = computed({
  get: () => model.value.fontSize,
  set: (value) => {
    model.value = { ...model.value, fontSize: value };
  },
});

const lineHeightModel = computed({
  get: () => model.value.lineHeight,
  set: (value) => {
    model.value = { ...model.value, lineHeight: value };
  },
});

const letterSpacingModel = computed({
  get: () => model.value.letterSpacing,
  set: (value) => {
    model.value = { ...model.value, letterSpacing: value };
  },
});

const textTransformModel = computed({
  get: () => model.value.textTransform,
  set: (value) => {
    model.value = { ...model.value, textTransform: value };
  },
});

</script>

<template>
  <div class="flex flex-col gap-4">
    <!-- Font Family -->
    <FontPicker
      :field="{ id: 'fontFamily', label: t('Font Family'), type: 'font' }"
      v-model="fontPickerModel"
    />

    <!-- Font Style -->
    <PropertyField
      :field="fontStyleField"
      v-model="fontStyleModel"
    />

    <!-- Font Size (Responsive via PropertyField) -->
    <PropertyField
      :field="fontSizeField"
      v-model="fontSizeModel"
    />

    <!-- Line Height (Responsive via PropertyField) -->
    <PropertyField
      :field="lineHeightField"
      v-model="lineHeightModel"
    />

    <!-- Letter Spacing -->
    <PropertyField
      :field="letterSpacingField"
      v-model="letterSpacingModel"
    />

    <!-- Text Transform -->
    <PropertyField
      :field="textTransformField"
      v-model="textTransformModel"
    />

    <!-- Delete Preset Button -->
    <div v-if="props.canDelete" class="pt-4 border-t border-zinc-200">
      <button
        type="button"
        class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg border border-red-200 hover:border-red-300 transition-colors"
        @click="handleDelete"
      >
        <i-heroicons-trash class="w-4 h-4" />
        {{ t('Delete Preset') }}
      </button>
    </div>
  </div>
</template>
