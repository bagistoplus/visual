<script setup lang="ts">
import useI18n from '../composables/i18n';
import { toTitleCase } from '../utils/strings';
import {
  getFontSizeOptions,
  getLineHeightOptions,
  getLetterSpacingOptions,
  getTextTransformOptions,
  formatFontWeight,
} from '../constants/typography';

interface Props {
  preset: any;
  label?: string;
}

const props = defineProps<Props>();

const { t } = useI18n();

const fontSizeOptions = getFontSizeOptions(t);
const lineHeightOptions = getLineHeightOptions(t);
const letterSpacingOptions = getLetterSpacingOptions(t);
const textTransformOptions = getTextTransformOptions(t);

const displayLabel = computed(() => {
  if (!props.label) {
    return '';
  }

  return props.preset?.name || toTitleCase(props.label);
});

const summary = computed(() => {
  const parts: string[] = [];

  if (props.preset?.fontFamily?.name) {
    parts.push(props.preset.fontFamily.name);
  }

  if (props.preset?.fontWeight) {
    parts.push(formatFontWeight(props.preset.fontWeight));
  }

  if (props.preset?.fontSize) {
    const fontSize = typeof props.preset.fontSize === 'string'
      ? props.preset.fontSize
      : (props.preset.fontSize._default || props.preset.fontSize[Object.keys(props.preset.fontSize)[0]]);

    if (fontSize) {
      const fontSizeLabel = fontSizeOptions.find(o => o.value === fontSize)?.label || fontSize;
      parts.push(fontSizeLabel);
    }
  }

  if (props.preset?.lineHeight) {
    const lineHeight = typeof props.preset.lineHeight === 'string'
      ? props.preset.lineHeight
      : (props.preset.lineHeight._default || props.preset.lineHeight[Object.keys(props.preset.lineHeight)[0]]);

    if (lineHeight) {
      const lineHeightLabel = lineHeightOptions.find(o => o.value === lineHeight)?.label || lineHeight;
      parts.push(lineHeightLabel);
    }
  }

  if (props.preset?.fontStyle === 'italic') {
    parts.push(t('Italic'));
  }

  if (props.preset?.letterSpacing && props.preset.letterSpacing !== 'normal') {
    const letterSpacingLabel = letterSpacingOptions.find(o => o.value === props.preset.letterSpacing)?.label;
    if (letterSpacingLabel) {
      parts.push(letterSpacingLabel);
    }
  }

  if (props.preset?.textTransform && props.preset.textTransform !== 'none') {
    const transformLabel = textTransformOptions.find(o => o.value === props.preset.textTransform)?.label;
    if (transformLabel) {
      parts.push(transformLabel);
    }
  }

  return parts.length > 0 ? parts.join(' • ') : t('Configure typography');
});
</script>

<template>
  <div class="flex flex-col gap-1">
    <div
      v-if="displayLabel"
      class="text-sm font-medium"
    >{{ displayLabel }}</div>
    <div class="text-xs text-gray-500">{{ summary }}</div>
  </div>
</template>
