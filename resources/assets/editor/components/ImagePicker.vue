<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Button } from '@craftile/editor/ui';
import { Dialog } from '@ark-ui/vue/dialog';
import { machine, connect } from "@zag-js/file-upload";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { useState } from '../state';
import useI18n from '../composables/i18n';
import { useHttpClient } from '../composables/http';
import type { Image, ImageFocalPoint, ImageSettingValue } from '../types';

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

type EditableImage = Image & ImageSettingValue;

defineProps<Props>();

const { t } = useI18n();
const { state } = useState();
const { get, postFormData } = useHttpClient();
const editor = useCraftileEditor()!;

const defaultFocalPoint = (): ImageFocalPoint => ({ x: 50, y: 50 });

function clampPercentage(value: number) {
  if (!Number.isFinite(value)) {
    return 50;
  }

  return Math.min(100, Math.max(0, Math.round(value)));
}

function normalizeFocalPoint(value?: Partial<ImageFocalPoint> | null): ImageFocalPoint {
  return {
    x: clampPercentage(Number(value?.x ?? 50)),
    y: clampPercentage(Number(value?.y ?? 50)),
  };
}

function imageUrl(path: string) {
  return isValidUrl(path) ? path : window.editorConfig.imagesBaseUrl + '/' + path;
}

function imageName(path: string) {
  return state.images.find(image => image.path === path)?.name ?? path;
}

function normalizeModelValue(value: string | ImageSettingValue | null): EditableImage | null {
  if (!value) {
    return null;
  }

  const path = typeof value === 'string' ? value : value.path;

  return {
    path,
    url: imageUrl(path),
    name: imageName(path),
    alt: typeof value === 'string' ? '' : value.alt ?? '',
    focalPoint: typeof value === 'string' ? defaultFocalPoint() : normalizeFocalPoint(value.focalPoint),
  };
}

function toSettingValue(value: EditableImage): ImageSettingValue {
  return {
    path: value.path,
    alt: value.alt ?? '',
    focalPoint: normalizeFocalPoint(value.focalPoint),
  };
}

const model = defineModel({
  set(value: EditableImage | null) {
    return value ? toSettingValue(value) : null;
  },

  get(v: string | ImageSettingValue | null): EditableImage | null {
    return normalizeModelValue(v);
  }
});

const opened = ref(false);
const metadataOpened = ref(false);
const uploadingImages = ref<Image[]>([]);
const currentValue = ref<EditableImage | null>(null);
const draftAlt = ref('');
const draftFocalPoint = ref<ImageFocalPoint>(defaultFocalPoint());

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
  const existingMetadata = model.value && model.value.path === image.path ? model.value : null;

  model.value = {
    ...image,
    alt: existingMetadata?.alt ?? '',
    focalPoint: existingMetadata?.focalPoint ?? defaultFocalPoint(),
  };
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

function openMetadataEditor() {
  if (!model.value) {
    return;
  }

  draftAlt.value = model.value.alt ?? '';
  draftFocalPoint.value = normalizeFocalPoint(model.value.focalPoint);
  metadataOpened.value = true;
}

function saveMetadata() {
  if (!model.value) {
    return;
  }

  model.value = {
    ...model.value,
    alt: draftAlt.value,
    focalPoint: normalizeFocalPoint(draftFocalPoint.value),
  };

  metadataOpened.value = false;
}

function updateDraftFocalPoint(event: PointerEvent | MouseEvent) {
  const target = event.currentTarget as HTMLElement;
  const rect = target.getBoundingClientRect();

  draftFocalPoint.value = {
    x: clampPercentage(((event.clientX - rect.left) / rect.width) * 100),
    y: clampPercentage(((event.clientY - rect.top) / rect.height) * 100),
  };
}

function startFocalPointDrag(event: PointerEvent) {
  updateDraftFocalPoint(event);

  const target = event.currentTarget as HTMLElement;
  target.setPointerCapture(event.pointerId);
}

function focalPointObjectPosition(focalPoint: ImageFocalPoint) {
  const normalized = normalizeFocalPoint(focalPoint);

  return `${normalized.x}% ${normalized.y}%`;
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
      @open-change="currentValue = model ? { ...model, focalPoint: { ...model.focalPoint } } : null"
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
            :style="{ objectPosition: focalPointObjectPosition(model.focalPoint) }"
          >
          <button
            class="absolute top-4 right-4 bg-zinc-700/30 text-zinc-200 p-px rounded"
            type="button"
            @click="removeImage"
          >
            <i-heroicons-x-mark class="w-4 h-4" />
          </button>
        </div>
        <div class="rounded-b p-3 bg-zinc-100 border-t border-white flex gap-2">
          <Dialog.Trigger class="text-sm flex-1 text-center bg-white border rounded py-1">
            {{ t('Change image') }}
          </Dialog.Trigger>
          <button
            type="button"
            class="text-sm flex-1 text-center bg-white border rounded py-1"
            @click="openMetadataEditor"
          >
            {{ t('Edit metadata') }}
          </button>
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

    <Teleport to=".__craftile">
      <Dialog.Root
        v-model:open="metadataOpened"
        :close-on-interact-outside="false"
      >
        <Dialog.Backdrop class="visual-image-metadata-backdrop fixed inset-0 z-[99] bg-black/20" />
        <Dialog.Positioner class="fixed inset-0 z-[100] flex h-screen w-screen items-center justify-center p-4">
          <Dialog.Content class="visual-image-metadata-content bg-white shadow-lg rounded-lg flex flex-col w-full max-w-md overflow-hidden">
            <header class="flex-none h-12 border-b border-zinc-200 flex gap-3 px-4 items-center justify-between">
              <Dialog.Title>{{ t('Edit image metadata') }}</Dialog.Title>
              <button
                type="button"
                @click="metadataOpened = false"
                class="cursor-pointer rounded-lg p-0.5 text-zinc-700 hover:bg-zinc-300"
              >
                <i-heroicons-x-mark class="w-5 h-5" />
              </button>
            </header>

            <section
              v-if="model"
              class="p-4 flex flex-col gap-4"
            >
              <label class="flex flex-col gap-1 text-sm font-medium text-zinc-700">
                {{ t('Alt text') }}
                <input
                  v-model="draftAlt"
                  type="text"
                  class="w-full rounded border border-zinc-300 px-3 py-2 text-sm font-normal focus:border-blue-500 focus:outline-none"
                >
              </label>

              <div class="flex flex-col gap-2">
                <span class="text-sm font-medium text-zinc-700">{{ t('Focal point') }}</span>
                <div
                  class="relative h-72 overflow-hidden rounded border border-zinc-300 bg-zinc-100 touch-none"
                  @pointerdown="startFocalPointDrag"
                  @pointermove="event => event.buttons === 1 && updateDraftFocalPoint(event)"
                >
                  <img
                    :src="model.url"
                    alt=""
                    class="h-full w-full object-cover"
                    draggable="false"
                  >
                  <div
                    class="absolute h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-blue-600 shadow"
                    :style="{ left: `${draftFocalPoint.x}%`, top: `${draftFocalPoint.y}%` }"
                  />
                </div>
                <p class="text-xs text-zinc-500">
                  {{ t('Focal point position') }}: {{ draftFocalPoint.x }}%, {{ draftFocalPoint.y }}%
                </p>
              </div>
            </section>

            <footer class="flex-none flex items-center gap-3 p-3 justify-end h-12 border-t border-zinc-200">
              <Button @click="metadataOpened = false">{{ t('Cancel') }}</Button>
              <Button
                variant="primary"
                @click="saveMetadata"
              >
                {{ t('Save') }}
              </Button>
            </footer>
          </Dialog.Content>
        </Dialog.Positioner>
      </Dialog.Root>
    </Teleport>
  </div>
</template>

<style scoped>
.visual-image-metadata-backdrop[data-state='open'] {
  animation: visual-image-metadata-backdrop-in 150ms ease-out;
}

.visual-image-metadata-backdrop[data-state='closed'] {
  animation: visual-image-metadata-backdrop-out 120ms ease-in;
}

.visual-image-metadata-content[data-state='open'] {
  animation: visual-image-metadata-content-in 180ms cubic-bezier(0.16, 1, 0.3, 1);
}

.visual-image-metadata-content[data-state='closed'] {
  animation: visual-image-metadata-content-out 120ms ease-in;
}

@keyframes visual-image-metadata-backdrop-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes visual-image-metadata-backdrop-out {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}

@keyframes visual-image-metadata-content-in {
  from {
    opacity: 0;
    transform: translateY(8px) scale(0.96);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes visual-image-metadata-content-out {
  from {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
  to {
    opacity: 0;
    transform: translateY(4px) scale(0.98);
  }
}
</style>
