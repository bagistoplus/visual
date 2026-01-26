type TranslateFunction = (key: string) => string;

export const getFontSizeOptions = (t: TranslateFunction) => [
  { value: 'xs', label: t('Extra Small') },
  { value: 'sm', label: t('Small') },
  { value: 'base', label: t('Base') },
  { value: 'lg', label: t('Large') },
  { value: 'xl', label: t('Extra Large') },
  { value: '2xl', label: t('2XL') },
  { value: '3xl', label: t('3XL') },
  { value: '4xl', label: t('4XL') },
  { value: '5xl', label: t('5XL') },
  { value: '6xl', label: t('6XL') },
  { value: '7xl', label: t('7XL') },
  { value: '8xl', label: t('8XL') },
  { value: '9xl', label: t('9XL') },
];

export const getLineHeightOptions = (t: TranslateFunction) => [
  { value: 'none', label: t('None') },
  { value: 'tight', label: t('Tight') },
  { value: 'snug', label: t('Snug') },
  { value: 'normal', label: t('Normal') },
  { value: 'relaxed', label: t('Relaxed') },
  { value: 'loose', label: t('Loose') },
];

export const getLetterSpacingOptions = (t: TranslateFunction) => [
  { value: 'tighter', label: t('Tighter') },
  { value: 'tight', label: t('Tight') },
  { value: 'normal', label: t('Normal') },
  { value: 'wide', label: t('Wide') },
  { value: 'wider', label: t('Wider') },
  { value: 'widest', label: t('Widest') },
];

export const getFontStyleOptions = (t: TranslateFunction) => [
  { value: 'normal', label: t('Normal') },
  { value: 'italic', label: t('Italic') },
];

export const getTextTransformOptions = (t: TranslateFunction) => [
  { value: 'none', label: t('None') },
  { value: 'capitalize', label: t('Capitalize') },
  { value: 'uppercase', label: t('Uppercase') },
  { value: 'lowercase', label: t('Lowercase') },
];

/**
 * Map font weights to display labels
 */
export const getFontWeightLabel = (weight: string): string => {
  const labels: Record<string, string> = {
    '100': 'Thin',
    '200': 'Extra Light',
    '300': 'Light',
    '400': 'Regular',
    '500': 'Medium',
    '600': 'Semi Bold',
    '700': 'Bold',
    '800': 'Extra Bold',
    '900': 'Black',
  };
  return labels[weight] || weight;
};

/**
 * Format weight for display (e.g., "400 - Regular")
 */
export const formatFontWeight = (weight: string): string => {
  return `${weight} - ${getFontWeightLabel(weight)}`;
};
