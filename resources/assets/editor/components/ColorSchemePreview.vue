<script setup lang="ts">
  import { ColorSchemeDefintion } from '../types';

  const props = defineProps<{
    id: string;
    scheme: { id: string; tokens: ColorSchemeDefintion } | ColorSchemeDefintion
  }>();

  const roles = ['primary', 'secondary', 'accent', 'neutral'] as const;
  type Role = typeof roles[number];

  // Extract tokens from the scheme object
  const colors = computed(() => {
    // If scheme has a tokens property, use it
    if ('tokens' in props.scheme && props.scheme.tokens) {
      return props.scheme.tokens;
    }
    // Otherwise assume scheme is the colors directly
    return props.scheme as ColorSchemeDefintion;
  });
</script>

<template>
  <div
    class="border border-zinc-300 rounded grid grid-cols-4 grid-rows-3 overflow-hidden cursor-pointer"
    :style="{ backgroundColor: colors.background, color: colors['on-background'] }"
  >
    <div
      title="Main background"
      class="col-start-1 row-start-1"
      :style="{ backgroundColor: colors.background }"
    />

    <div
      class="col-start-1 row-start-2"
      :style="{ backgroundColor: colors.surface }"
      title="Surface color"
    />

    <div
      class="col-start-1 row-start-3"
      :style="{ backgroundColor: colors['surface-alt'] }"
      title="Alternative surface color"
    />

    <div class="px-2 py-1 col-span-3 col-start-2 row-start-1 row-span-3">
      <div class="mb-px font-semibold text-xs">
        {{ id }}
      </div>
      <div class="grid grid-cols-2 gap-2">
        <template
          v-for="role in roles"
          :key="role"
        >
          <div
            :title="role + ' color'"
            class="rounded flex items-center justify-center"
            :style="{ backgroundColor: colors[role as Role], color: colors[`on-${role}` as `on-${Role}`] }"
          >
            <span class="font-semibold">A</span>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
