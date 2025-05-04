<script setup lang="ts">
  import { ColorSchemeDefintion } from '../types';

  const emit = defineEmits<{
    (event: 'update', scheme: ColorSchemeDefintion): void;
  }>();

  const props = defineProps<{
    id: string;
  }>();

  const scheme = defineModel<ColorSchemeDefintion>();
  const descriptions = {
    'background': 'Main background color. For page background and full-width sections',
    'on-background': 'Default text color',

    'primary': 'Primary color. For primary buttons, link, CTAs',
    'on-primary': 'Text/icons on primary backgrounds',
    'secondary': 'Secondary color. For Headings, badges, accents',
    'on-secondary': 'Text/icons on secondary backgrounds',
    'accent': 'Highlight color. For featured content, banners, badges',
    'on-accent': 'Text/icons on accent elements',
    'neutral': 'Neutral color',
    'on-neutral': 'On neutral color',

    'surface': 'Surface color. For components or sections background',
    'on-surface': 'Section text, icons',
    'surface-alt': 'Alternative surface color. For hover states, dropdowns',
    'on-surface-alt': 'Text/icons on alternative surface',

    'error': 'Errors, danger zones',
    'on-error': 'Text/icons on error messages',
    'warning': 'Notices, validation warnings',
    'on-warning': 'Text/icons on warning backgrounds',
    'success': 'Alerts, tags, confirmations',
    'on-success': 'Text/icons on success backgrounds',
    'info': 'Tooltips, banners, guidance',
    'on-info': 'Text/icons on info sections',
  };

  function onUpdateColor(key: string, value: string) {
    (scheme.value as any)[key] = value;
    emit('update', scheme.value!)
  }
</script>

<template>
  <div>
    <ColorSchemePreview
      :scheme="scheme!"
      :id="id"
    />

    <div class="mt-4 space-y-4">
      <div v-for="(color, key) in scheme">
        <label class="capitalize">{{ key.replace('-', ' ') }}</label>
        <ColorPicker
          :model-value="color"
          @update:model-value="(value: string) => onUpdateColor(key, value)"
        />
        <p class="text-xs leading-snug italic mt-1">{{ descriptions[key] }}</p>
      </div>
    </div>
  </div>
</template>