<script setup lang="ts">
import { PropertyField } from '@craftile/editor/ui';
import useI18n from '../composables/i18n';
import { useBunnyFonts } from '../composables/useBunnyFonts';
import FontPicker from './FontPicker.vue';
import {
  getFontSizeOptions,
  getLineHeightOptions,
  getLetterSpacingOptions,
  getFontStyleOptions,
  getTextTransformOptions,
  formatFontWeight,
} from '../constants/typography';

interface Font {
  slug: string;
  name: string;
  weights: number[];
  styles: string[];
}

interface TypographyPresetValue {
  fontFamily: Font | null;
  fontStyle: string;
  fontWeight: number;
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
const { findFont, isFetched } = useBunnyFonts();

const fontSizeOptions = getFontSizeOptions(t);
const lineHeightOptions = getLineHeightOptions(t);
const letterSpacingOptions = getLetterSpacingOptions(t);
const fontStyleOptions = getFontStyleOptions(t);
const textTransformOptions = getTextTransformOptions(t);

const model = defineModel<TypographyPresetValue>({
  default: () => ({
    fontFamily: null,
    fontStyle: 'normal',
    fontWeight: 400,
    fontSize: 'base',
    lineHeight: 'normal',
    letterSpacing: 'normal',
    textTransform: 'none',
  }),
  get(v: any) {
    if (v && typeof v.fontWeight === 'string') {
      return {
        ...v,
        fontWeight: parseInt(v.fontWeight, 10),
      };
    }
    return v;
  },
  set(v: TypographyPresetValue) {
    return v;
  },
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

const fontStyleField = computed(() => {
  const styles = model.value.fontFamily?.styles || ['normal'];

  return {
    id: 'fontStyle',
    label: t('Font Style'),
    type: 'select',
    options: styles.map(style => {
      const option = fontStyleOptions.find(o => o.value === style);
      return option || { value: style, label: style };
    }),
  };
});

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
    return model.value.fontFamily;
  },
  set: (value: Font | null) => {
    if (!value) {
      model.value = { ...model.value, fontFamily: null, fontWeight: 400, fontStyle: 'normal' };
      return;
    }

    const currentWeight = model.value.fontWeight;
    const newWeight = value.weights.includes(currentWeight)
      ? currentWeight
      : (value.weights.includes(400) ? 400 : value.weights[0]);

    const currentStyle = model.value.fontStyle;
    const newStyle = value.styles.includes(currentStyle)
      ? currentStyle
      : (value.styles.includes('normal') ? 'normal' : value.styles[0]);

    model.value = {
      ...model.value,
      fontFamily: {
        slug: value.slug,
        name: value.name,
        weights: value.weights,
        styles: value.styles,
      },
      fontWeight: newWeight,
      fontStyle: newStyle,
    };
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

const fontWeightModel = computed({
  get: () => model.value.fontWeight,
  set: (value) => {
    model.value = { ...model.value, fontWeight: value };
  },
});

watch(
  () => ({ fetched: isFetched.value, fontFamily: model.value.fontFamily }),
  ({ fetched, fontFamily }) => {
    if (!fetched || !fontFamily) {
      return;
    }

    const slug = typeof fontFamily === 'string'
      ? fontFamily
      : fontFamily.slug;

    const fullFont = findFont(slug);

    if (fullFont && (typeof fontFamily === 'string' || !fontFamily.weights || fontFamily.weights.length === 1)) {
      model.value = {
        ...model.value,
        fontFamily: fullFont,
      };
    }
  },
  { immediate: true, deep: true }
);

const fontWeightField = computed(() => {
  const weights = model.value.fontFamily?.weights || [400];

  return {
    id: 'fontWeight',
    label: t('Font Weight'),
    type: 'select',
    options: weights.map((weight: number) => ({
      value: weight,
      label: formatFontWeight(String(weight)),
    })),
  };
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

    <!-- Font Weight -->
    <PropertyField
      v-if="model.fontFamily"
      :field="fontWeightField"
      v-model="fontWeightModel"
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
    <div
      v-if="props.canDelete"
      class="pt-4 border-t border-zinc-200"
    >
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
