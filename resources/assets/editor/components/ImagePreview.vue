<script setup lang="ts">
  import { Image } from '../types';

  const props = defineProps<{ image: Image, selected?: boolean }>();
</script>

<template>
  <div
    class="group flex flex-col items-center justify-center p-3 gap-3 rounded-lg cursor-pointer hover:bg-neutral-100"
    :class="{ 'bg-neutral-100': selected, 'pointer-events-none': image.uploading }"
  >
    <div class="border rounded-lg overflow-hidden p-1 relative bg-white">
      <img
        class="hover:bg-neutral-200 object-cover w-full aspect-square rounded"
        :class="{ 'bg-neutral-200': selected }"
        :src="image.url"
      >
      <div
        class="absolute p-2 rounded-md inset-1 group-hover:block group-hover:bg-neutral-900/10"
        :class="{ hidden: !selected }"
      >
        <Checkbox :model-value="selected" />
      </div>
      <div
        v-show="image.uploading"
        class="absolute bg-black/50 inset-0 flex items-center justify-center"
      >
        <svg
          class="animate-spin h-5 w-5 text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
      </div>
    </div>

    <p class="text-xs text-center truncate overflow-hidden max-w-full">
      {{ image.name }}
    </p>
  </div>
</template>