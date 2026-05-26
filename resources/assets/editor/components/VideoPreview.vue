<script setup lang="ts">
import type { Video, VideoSettingValue } from '../types';

type PreviewVideo = Partial<Video> & Partial<VideoSettingValue> & {
  name?: string;
  previewUrl?: string | null;
  externalId?: string | null;
};

const props = defineProps<{
  video: PreviewVideo;
  selected?: boolean;
  compact?: boolean;
}>();

const isExternal = computed(() => props.video.mode === 'external');
const isYoutube = computed(() => props.video.host === 'youtube');
const isVimeo = computed(() => props.video.host === 'vimeo');
const isNativePreview = computed(() => !isExternal.value || (!isYoutube.value && !isVimeo.value));
const title = computed(() => props.video.name || props.video.url || props.video.path || props.video.host || 'Video');
</script>

<template>
  <div
    class="group flex flex-col items-center justify-center gap-3 rounded-lg"
    :class="[
      compact ? 'p-0' : 'p-3 cursor-pointer hover:bg-neutral-100',
      { 'bg-neutral-100': selected, 'pointer-events-none': video.uploading },
    ]"
  >
    <div class="border rounded-lg overflow-hidden p-px relative bg-white w-full">
      <div
        class="relative rounded-sm bg-zinc-100 overflow-hidden aspect-video w-full flex items-center justify-center"
        :class="{ 'bg-neutral-200': selected }"
      >
        <video
          v-if="isNativePreview"
          :src="video.url"
          class="h-full w-full object-cover"
          muted
          preload="metadata"
        />
        <img
          v-else-if="isYoutube && video.previewUrl"
          :src="video.previewUrl"
          alt=""
          class="h-full w-full object-cover"
        >
        <div
          v-else
          class="h-full w-full flex flex-col items-center justify-center gap-1 text-zinc-500"
        >
          <i-heroicons-play-circle class="w-9 h-9" />
          <span class="text-xs font-medium">{{ video.host || 'Video' }}</span>
          <span
            v-if="video.externalId"
            class="text-[11px] text-zinc-400"
          >{{ video.externalId }}</span>
        </div>

        <div
          v-if="isExternal || isYoutube"
          class="absolute inset-0 flex items-center justify-center pointer-events-none"
        >
          <div class="rounded-full bg-black/55 text-white p-1.5">
            <i-heroicons-play class="w-5 h-5" />
          </div>
        </div>
      </div>

      <div
        v-if="!compact"
        class="absolute p-2 rounded-md inset-1 group-hover:block group-hover:bg-neutral-900/10"
        :class="{ hidden: !selected }"
      >
        <Checkbox :model-value="selected" />
      </div>

      <div
        v-show="video.uploading"
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
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          />
        </svg>
      </div>
    </div>

    <p
      v-if="!compact"
      class="text-xs text-center truncate overflow-hidden max-w-full"
    >
      {{ title }}
    </p>
  </div>
</template>
