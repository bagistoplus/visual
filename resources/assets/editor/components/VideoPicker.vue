<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Button } from '@craftile/editor/ui';
import { Dialog } from '@ark-ui/vue/dialog';
import { machine, connect } from '@zag-js/file-upload';
import { normalizeProps, useMachine } from '@zag-js/vue';
import { useState } from '../state';
import useI18n from '../composables/i18n';
import { useHttpClient } from '../composables/http';
import type { Video, VideoSettingValue } from '../types';

const props = defineProps<{
  field: PropertyField;
  modelValue: VideoSettingValue | string | null;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: VideoSettingValue | string | null];
}>();

const { t } = useI18n();
const { state } = useState();
const { get, postFormData } = useHttpClient();
const editor = useCraftileEditor()!;

const opened = ref(false);
const uploadingVideos = ref<Video[]>([]);
const currentValue = ref<VideoSettingValue | string | null>(null);

function videoUrl(path: string) {
  return path.startsWith('http://') || path.startsWith('https://')
    ? path
    : window.editorConfig.videosBaseUrl + '/' + path;
}

function videoName(path: string) {
  return state.videos.find(video => video.path === path)?.name ?? path;
}

function selectedPath(value: VideoSettingValue | string | null): string | null {
  if (!value) {
    return null;
  }

  if (typeof value === 'string') {
    return value.startsWith('http://') || value.startsWith('https://') ? null : value;
  }

  if (value.upload?.path) {
    return value.upload.path;
  }

  return value.mode === 'upload' && value.path ? value.path : null;
}

const selectedVideo = computed<Video | null>(() => {
  const path = selectedPath(props.modelValue);

  if (!path) {
    return null;
  }

  return state.videos.find(video => video.path === path) ?? {
    path,
    url: videoUrl(path),
    name: videoName(path),
  };
});

const service = useMachine(machine, {
  id: 'videopicker',
  accept: 'video/mp4,video/x-m4v,video/webm,video/ogg',
  maxFiles: 10,
  onFileAccept(details) {
    if (details.files.length === 0) {
      return;
    }

    uploadingVideos.value = details.files.map(file => ({
      url: URL.createObjectURL(file),
      path: file.name,
      name: file.name,
      uploading: true,
    }));

    const formData = new FormData();

    details.files.forEach(file => {
      formData.append('video[]', file);
    });

    uploadVideo(formData);
  },
});

const fileUpload = computed(() => connect(service, normalizeProps));
const uploadRequest = postFormData<Video[]>(window.editorConfig.routes.uploadVideo);

uploadRequest.onSuccess((data) => {
  state.videos = [...data, ...state.videos];
  uploadingVideos.value = [];
  onVideoSelect(state.videos[0]);
  fileUpload.value.clearFiles();
});

uploadRequest.onError((error) => {
  uploadingVideos.value = [];
  fileUpload.value.clearFiles();

  editor.ui.toast({
    type: 'error',
    title: t('Failed to upload video'),
    description: error.message || error.toString(),
  });
});

async function uploadVideo(formData: FormData) {
  uploadRequest.execute(formData);
}

function onVideoSelect(video: Video) {
  emit('update:modelValue', {
    mode: 'upload',
    path: video.path,
  });
}

function onCancel() {
  emit('update:modelValue', currentValue.value);
  opened.value = false;
}

function onConfirm() {
  opened.value = false;
}

function onRemove() {
  emit('update:modelValue', null);
}

const { data: videos, execute: fetchVideos } = get<Video[]>(window.editorConfig.routes.listVideos);

watch(videos, (newVideos) => {
  if (newVideos) {
    state.videos = newVideos;
  }
});

onMounted(() => {
  if (!state.videos || state.videos.length === 0) {
    fetchVideos();
  }
});
</script>

<template>
  <Dialog.Root
    v-model:open="opened"
    :modal="false"
    :close-on-interact-outside="false"
    @open-change="currentValue = modelValue"
  >
    <Dialog.Trigger
      v-if="!selectedVideo"
      class="min-h-24 w-full border border-dashed rounded flex flex-col items-center justify-center text-blue-600 text-sm bg-zinc-200 hover:bg-zinc-100 hover:text-blue-800"
    >
      {{ t('Select video') }}
    </Dialog.Trigger>
    <Dialog.Trigger
      v-else
      as-child
    >
      <button
        type="button"
        class="group relative block w-full rounded overflow-hidden text-left"
      >
        <VideoPreview
          :video="{ ...selectedVideo, mode: 'upload' }"
          compact
        />

        <div class="absolute inset-0 hidden group-hover:flex items-center justify-center bg-black/50 text-white text-sm font-medium pointer-events-none">
          {{ t('Change') }}
        </div>

        <span
          class="absolute top-2 right-2 bg-zinc-700/60 text-zinc-100 p-1 rounded cursor-pointer"
          role="button"
          :title="t('Remove video')"
          @click.stop="onRemove"
        >
          <i-heroicons-x-mark class="w-3.5 h-3.5" />
        </span>
      </button>
    </Dialog.Trigger>

    <Dialog.Positioner class="flex absolute inset-0 z-50 h-full w-full items-center justify-center">
      <Dialog.Content class="bg-white shadow flex flex-col w-full h-full overflow-hidden">
        <header class="flex-none h-12 border-b border-zinc-200 flex gap-3 px-4 items-center justify-between">
          <Dialog.Title>{{ field.label || t('Video Picker') }}</Dialog.Title>
          <button
            @click="onCancel"
            class="cursor-pointer rounded-lg p-0.5 text-zinc-700 hover:bg-zinc-300"
          >
            <i-heroicons-x-mark class="w-5 h-5" />
          </button>
        </header>

        <section class="flex-1 flex flex-col gap-3 min-h-0 p-3 overflow-y-auto">
          <div
            v-bind="fileUpload.getRootProps()"
            accept="video/mp4,video/webm,video/ogg"
          >
            <div
              v-bind="fileUpload.getDropzoneProps()"
              class="flex flex-col gap-3 items-center justify-center h-32 bg-zinc-50/50 border border-zinc-300 border-dashed rounded-lg"
            >
              <p>{{ t('Drop your videos here') }}</p>
              <button
                v-bind="fileUpload.getTriggerProps()"
                class="cursor-pointer bg-blue-500 text-white shadow-lg rounded border px-2.5 py-1.5 text-sm"
              >
                {{ t('Add videos') }}
              </button>
            </div>
            <input v-bind="fileUpload.getHiddenInputProps()">
          </div>

          <div class="grid grid-cols-2 gap-3">
            <VideoPreview
              v-for="video in uploadingVideos"
              :key="video.path"
              :video="video"
            />
            <VideoPreview
              v-for="video in state.videos"
              :key="video.path"
              :video="{ ...video, mode: 'upload' }"
              :selected="!!selectedVideo && selectedVideo.path === video.path"
              @click="onVideoSelect(video)"
            />
          </div>
        </section>

        <footer class="flex-none flex items-center gap-3 p-3 justify-end h-12 border-t border-zinc-200">
          <Button @click="onCancel">{{ t('Cancel') }}</Button>
          <Button
            variant="primary"
            @click="onConfirm"
          >
            {{ t('Select') }}
          </Button>
        </footer>
      </Dialog.Content>
    </Dialog.Positioner>
  </Dialog.Root>
</template>
