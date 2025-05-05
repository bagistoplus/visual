<script setup lang="ts">
  import { ColorSchemeDefintion } from '../types';

  const props = defineProps<{
    id: string;
    scheme: ColorSchemeDefintion
  }>();

  const roles = ['primary', 'secondary', 'accent', 'neutral'] as const;
  type Role = typeof roles[number];
</script>

<template>
  <div
    class="border rounded grid grid-cols-4 grid-rows-3 overflow-hidden cursor-pointer"
    :style="{ backgroundColor: scheme.background, color: scheme['on-background'] }"
  >
    <div
      title="Main background"
      class="col-start-1 row-start-1"
      :style="{ backgroundColor: scheme.background }"
    />

    <div
      class="col-start-1 row-start-2"
      :style="{ backgroundColor: scheme.surface }"
      title="Surface color"
    />

    <div
      class="col-start-1 row-start-3"
      :style="{ backgroundColor: scheme['surface-alt'] }"
      title="Alternative surface color"
    />

    <div class="px-2 py-1 col-span-3 col-start-2 row-start-1 row-span-3">
      <div class="mb-px font-semibold">
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
            :style="{ backgroundColor: scheme[role as Role], color: scheme[`on-${role}` as `on-${Role}`] }"
          >
            <span class="font-semibold">A</span>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>