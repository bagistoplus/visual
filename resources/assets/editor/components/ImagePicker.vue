<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Button } from '@craftile/editor/ui';
import { Dialog } from '@ark-ui/vue/dialog';
import { machine, connect } from "@zag-js/file-upload";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { useState } from '../state';
import useI18n from '../composables/i18n';
import { useHttpClient } from '../composables/http';
import { Image } from '../types';

// @see https://stackoverflow.com/a/5717133
const isValidUrl = (str: string) => {
  const pattern = new RegExp(
    '^(https?:\\/\\/)?' + // protocol
    '((localhost)|' + // allow localhost
    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,})|' + // domain name
    '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
    '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
    '(\\#[-a-z\\d_]*)?$',
    'i');

  return str.startsWith('/') || !!pattern.test(str);
}

interface Props {
  field: PropertyField;
}

defineProps<Props>();

const { t } = useI18n();
const { state } = useState();
const { get, postFormData } = useHttpClient();
const editor = useCraftileEditor()!;

const model = defineModel({
  set(value: Image | null) {
    return value ? value.path : null;
  },

  get(v: string | null): Image | null {
    if (!v) {
      return null;
    }

    return { path: v, url: isValidUrl(v) ? v : window.editorConfig.imagesBaseUrl + '/' + v, name: v };
  }
});

const opened = ref(false);
const uploadingImages = ref<Image[]>([]);
const currentValue = ref<Image>();

const service = useMachine(machine, {
  id: "imagepicker",
  accept: "image/*",
  maxFiles: 10,
  onFileAccept(details) {
    if (details.files.length === 0) {
      return;
    }

    uploadingImages.value = details.files.map(file => ({
      url: URL.createObjectURL(file),
      path: file.name,
      name: file.name,
      uploading: true
    }));

    const formData = new FormData;

    details.files.forEach(file => {
      formData.append('image[]', file);
    });

    uploadImage(formData);
  },
});

const fileUpload = computed(() => connect(service, normalizeProps));

const uploadRequest = postFormData<Image[]>(window.editorConfig.routes.uploadImage);

uploadRequest.onSuccess((data) => {
  state.images = [...data, ...state.images];
  uploadingImages.value = [];
  onImageSelect(state.images[0]);
  fileUpload.value.clearFiles();
});

uploadRequest.onError((error) => {
  uploadingImages.value = [];
  fileUpload.value.clearFiles();

  editor.ui.toast({
    type: 'error',
    title: t('Failed to upload image'),
    description: error.message || error.toString(),
  });
});

async function uploadImage(formData: FormData) {
  uploadRequest.execute(formData);
}

function onImageSelect(image: Image) {
  model.value = image;
}

function onCancel() {
  model.value = currentValue.value;
  opened.value = false;
}

function onConfirm() {
  opened.value = false;
}

function removeImage() {
  model.value = null;
}

const { data: images, execute: fetchImages } = get<Image[]>(window.editorConfig.routes.listImages);

watch(images, (newImages) => {
  if (newImages) {
    state.images = newImages;
  }
});

onMounted(() => {
  if (!state.images || state.images.length === 0) {
    fetchImages();
  }
});
</script>

<template>
  <div>
    <label
      v-if="field.label"
      class="text-sm block mb-1 font-medium text-gray-700"
    >
      {{ field.label }}
    </label>

    <Dialog.Root
      v-model:open="opened"
      :modal="false"
      :close-on-interact-outside="false"
      @open-change="currentValue = model || undefined"
    >
      <div
        v-if="!model"
        class="min-h-24 border border-dashed rounded flex flex-col items-center justify-center"
      >
        <Dialog.Trigger class="text-blue-600 text-sm bg-zinc-200 rounded px-2.5 py-1.5 hover:bg-zinc-100 hover:text-blue-800">
          {{ t('Select image') }}
        </Dialog.Trigger>
      </div>
      <div v-else>
        <div class="rounded-t p-3 bg-zinc-100 relative">
          <img
            :src="model.url"
            alt=""
            class="object-cover h-32 w-full object-center"
          >
          <button
            class="absolute top-4 right-4 bg-zinc-700/30 text-zinc-200 p-px rounded"
            @click="removeImage"
          >
            <i-heroicons-x-mark class="w-4 h-4" />
          </button>
        </div>
        <div class="rounded-b p-3 bg-zinc-100 border-t border-white">
          <Dialog.Trigger class="text-sm w-full text-center bg-white border rounded py-1">
            {{ t('Change image') }}
          </Dialog.Trigger>
        </div>
      </div>

      <Dialog.Positioner class="flex absolute inset-0 z-50 h-full w-full items-center justify-center">
        <Dialog.Content class="bg-white shadow flex flex-col w-full h-full overflow-hidden">
          <header class="flex-none h-12 border-b border-zinc-200 flex gap-3 px-4 items-center justify-between">
            <Dialog.Title>{{ field.label || t('Image Picker') }}</Dialog.Title>
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
              accept="image/*"
            >
              <div
                v-bind="fileUpload.getDropzoneProps()"
                class="flex flex-col gap-3 items-center justify-center h-32 bg-zinc-50/50 border border-zinc-300 border-dashed rounded-lg"
              >
                <p>{{ t('Drop your images here') }}</p>
                <button
                  v-bind="fileUpload.getTriggerProps()"
                  class="cursor-pointer bg-blue-500 text-white shadow-lg rounded border px-2.5 py-1.5 text-sm"
                >
                  {{ t('Add images') }}
                </button>
              </div>
              <input v-bind="fileUpload.getHiddenInputProps()" />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <ImagePreview
                v-for="image in uploadingImages"
                :key="image.path"
                :image="image"
              />
              <ImagePreview
                v-for="image in state.images"
                :key="image.path"
                :image="image"
                :selected="!!model && model.path === image.path"
                @click="onImageSelect(image)"
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
  </div>
</template>